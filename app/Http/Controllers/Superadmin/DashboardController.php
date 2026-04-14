<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\MacMachine;
use App\Models\Message;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'clients'          => Client::count(),
            'projects'         => Project::where('status', 'active')->count(),
            'machines_online'  => MacMachine::where('status', 'online')->count(),
            'pending_messages' => Message::where('status', 'pending')->count(),
            'mrr'              => Invoice::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->sum('total_ttc'),
            'unpaid_total'     => Invoice::whereIn('status', ['sent', 'overdue'])->sum('total_ttc'),
        ];

        $recentInvoices = Invoice::with('client')
            ->latest()
            ->limit(5)
            ->get();

        $offlineMachines = MacMachine::with('project.client')
            ->where('status', 'offline')
            ->latest('last_seen_at')
            ->limit(5)
            ->get();

        return view('superadmin.dashboard', compact('stats', 'recentInvoices', 'offlineMachines'));
    }
}
