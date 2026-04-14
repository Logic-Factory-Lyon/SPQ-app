<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $client = auth()->user()->client;
        abort_if(! $client, 403);

        $projects = $client->projects()->withCount('members')->active()->get();
        $unpaidInvoices = $client->invoices()->whereIn('status', ['sent', 'overdue'])->latest()->limit(5)->get();
        $pendingQuotes = $client->quotes()->where('status', 'sent')->latest()->limit(3)->get();
        $outstandingBalance = $client->outstanding_balance;

        return view('client.dashboard', compact('client', 'projects', 'unpaidInvoices', 'pendingQuotes', 'outstandingBalance'));
    }
}
