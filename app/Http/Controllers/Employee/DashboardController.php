<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ProjectMember;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $member = auth()->user()->projectMembers()->with(['project', 'agent.macMachine'])->first();
        $recentConversations = $member
            ? $member->conversations()->with('latestMessage')->latest()->limit(5)->get()
            : collect();

        return view('employee.dashboard', compact('member', 'recentConversations'));
    }
}
