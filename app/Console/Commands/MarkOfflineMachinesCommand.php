<?php
namespace App\Console\Commands;

use App\Models\MacMachine;
use Illuminate\Console\Command;

class MarkOfflineMachinesCommand extends Command
{
    protected $signature = 'machines:mark-offline {--minutes=5 : Stale threshold in minutes}';
    protected $description = 'Mark Mac Mini machines as offline if they have not sent a heartbeat recently.';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');

        $count = MacMachine::where('status', 'online')
            ->where('last_seen_at', '<', now()->subMinutes($minutes))
            ->update(['status' => 'offline']);

        if ($count > 0) {
            $this->info("{$count} machine(s) marquées offline.");
        }

        return self::SUCCESS;
    }
}
