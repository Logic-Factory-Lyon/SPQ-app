<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Project;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        // ── Telegram bot agent ──────────────────────────────────────────
        if ($request->filled('telegram_bot_username')) {
            $data = $request->validate([
                'name'                 => 'required|string|max:100',
                'telegram_bot_username'=> 'required|string|max:100',
                'telegram_bot_token'   => 'nullable|string|max:200|unique:agents,telegram_bot_token',
            ]);
            // Normalize: strip leading @
            $data['telegram_bot_username'] = ltrim($data['telegram_bot_username'], '@');
            $data['project_id'] = $project->id;

            Agent::create($data);

            return redirect()->route('admin.projects.show', $project)
                ->with('success', 'Bot Telegram ajouté.');
        }

        // ── Mac Machine agent (legacy) ──────────────────────────────────
        $data = $request->validate([
            'mac_machine_id' => 'required|exists:mac_machines,id',
            'name'           => 'required|string|max:100',
            'profile'        => 'required|string|max:100',
        ]);

        $machine = $project->macMachines()->find($data['mac_machine_id']);
        abort_if(! $machine, 403, 'Cette machine n\'appartient pas à ce projet.');

        Agent::create($data);

        return redirect()->route('admin.projects.show', $project)->with('success', 'Agent créé.');
    }

    public function destroy(Project $project, Agent $agent): RedirectResponse
    {
        if ($agent->mac_machine_id) {
            abort_if($agent->macMachine->project_id !== $project->id, 404);
        } else {
            abort_if($agent->project_id !== $project->id, 404);
        }

        $agent->delete();

        return redirect()->route('admin.projects.show', $project)->with('success', 'Agent supprimé.');
    }

    /**
     * Register SPQ as the Telegram webhook for this bot.
     * WARNING: this will disable OpenClaw's long-polling on the Mac Mini.
     * Only use if you want conversation history visible in SPQ.
     */
    public function registerWebhook(Project $project, Agent $agent): RedirectResponse
    {
        abort_if(! $agent->telegram_bot_token, 400, 'Token Telegram non configuré pour cet agent.');

        if ($agent->mac_machine_id) {
            abort_if($agent->macMachine->project_id !== $project->id, 404);
        } else {
            abort_if($agent->project_id !== $project->id, 404);
        }

        $url    = url("/api/telegram/webhook/{$agent->id}");
        $secret = TelegramService::webhookSecret($agent->telegram_bot_token);
        $ok     = app(TelegramService::class)->setWebhook($agent->telegram_bot_token, $url, $secret);

        if ($ok) {
            return redirect()->route('admin.projects.show', $project)
                ->with('success', "Webhook enregistré. Attention : le polling OpenClaw sur le Mac Mini ne recevra plus les messages.");
        }

        return redirect()->route('admin.projects.show', $project)
            ->with('error', 'Échec de l\'enregistrement du webhook. Vérifiez le token Telegram.');
    }
}
