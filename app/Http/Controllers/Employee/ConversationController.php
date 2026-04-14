<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ProjectMember;
use App\Services\MacMachine\OpenClawDispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(
        private readonly OpenClawDispatchService $dispatcher,
    ) {}

    private function getMember(): ProjectMember
    {
        $member = auth()->user()->projectMembers()->with('agent.macMachine')->first();
        abort_if(! $member, 403, 'Vous n\'êtes associé à aucun projet.');
        return $member;
    }

    public function index(): View
    {
        $member = $this->getMember();
        $conversations = $member->conversations()
            ->with('latestMessage')
            ->latest()
            ->paginate(20);
        $agent = $member->agent;
        $machine = $agent?->macMachine;
        return view('employee.conversations.index', compact('conversations', 'member', 'agent', 'machine'));
    }

    public function store(Request $request): RedirectResponse
    {
        $member = $this->getMember();
        $request->validate(['title' => 'nullable|string|max:255']);

        $conversation = $member->conversations()->create([
            'title' => $request->get('title') ?: 'Nouvelle conversation du ' . now()->format('d/m/Y H:i'),
        ]);

        return redirect()->route('employee.conversations.show', $conversation);
    }

    public function show(Conversation $conversation): View
    {
        $member = $this->getMember();
        abort_if($conversation->project_member_id !== $member->id, 403);

        $messages = $conversation->messages()
            ->where(fn($q) => $q->where('direction', 'in')->orWhereIn('status', ['response', 'error']))
            ->orderBy('created_at')
            ->get();
        $hasPending = $conversation->messages()
            ->where('direction', 'out')->whereIn('status', ['pending', 'processing'])->exists();
        $agent = $member->agent;
        $machine = $agent?->macMachine;

        return view('employee.conversations.show', compact('conversation', 'messages', 'hasPending', 'member', 'agent', 'machine'));
    }

    public function destroy(Conversation $conversation): RedirectResponse
    {
        $member = $this->getMember();
        abort_if($conversation->project_member_id !== $member->id, 403);
        $conversation->delete();
        return redirect()->route('employee.conversations.index');
    }

    public function sendMessage(Request $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        $member = $this->getMember();
        abort_if($conversation->project_member_id !== $member->id, 403);

        $validated = $request->validate(['content' => 'required|string|max:10000']);

        // Save inbound (user → system) message
        Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'in',
            'content'   => $validated['content'],
            'status'    => 'done',
        ]);

        // Queue outbound (system → openclaw) message for the Mac Mini daemon
        $this->dispatcher->dispatch($conversation, $validated['content']);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'queued']);
        }

        return redirect()->route('employee.conversations.show', $conversation);
    }

    /**
     * AJAX polling endpoint — returns new messages since a given message ID.
     */
    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $member = $this->getMember();
        abort_if($conversation->project_member_id !== $member->id, 403);

        $afterId = (int) $request->get('after_id', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->where(fn($q) => $q->where('direction', 'in')->orWhereIn('status', ['response', 'error']))
            ->orderBy('created_at')
            ->get()
            ->map(fn(Message $m) => [
                'id'         => $m->id,
                'direction'  => $m->direction,
                'content'    => $m->content,
                'status'     => $m->status,
                'created_at' => $m->created_at->format('H:i'),
            ]);

        return response()->json(['messages' => $messages]);
    }
}
