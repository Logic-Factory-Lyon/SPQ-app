<?php
namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

class ResetStuckMessagesCommand extends Command
{
    protected $signature = 'messages:reset-stuck {--minutes=10 : Threshold in minutes}';
    protected $description = 'Reset messages stuck in "processing" status back to "pending".';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');

        $count = Message::where('status', 'processing')
            ->where('updated_at', '<', now()->subMinutes($minutes))
            ->update(['status' => 'pending']);

        if ($count > 0) {
            $this->info("{$count} message(s) réinitialisé(s) en statut pending.");
        }

        return self::SUCCESS;
    }
}
