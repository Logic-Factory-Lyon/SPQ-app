<?php
namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ProjectMember;
use Illuminate\View\View;

class ConversationController extends Controller
{
    private function getManagerProjectId(): int
    {
        $member = auth()->user()->projectMembers()->where('role', 'manager')->first();
        abort_if(! $member, 403);
        return $member->project_id;
    }

    public function index(): View
    {
        $projectId = $this->getManagerProjectId();
        $conversations = Conversation::whereHas('projectMember', fn($q) => $q->where('project_id', $projectId))
            ->with(['projectMember.user', 'latestMessage'])
            ->latest()
            ->paginate(25);

        return view('manager.conversations.index', compact('conversations'));
    }

    public function show(Conversation $conversation): View
    {
        $projectId = $this->getManagerProjectId();
        abort_if($conversation->projectMember->project_id !== $projectId, 403);

        $messages = $conversation->messages()->orderBy('created_at')->get();
        return view('manager.conversations.show', compact('conversation', 'messages'));
    }
}
