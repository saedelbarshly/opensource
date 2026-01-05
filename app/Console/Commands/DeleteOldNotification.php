<?php

namespace App\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class DeleteOldNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications that are older than one month to clean up the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to delete old notifications...');

        // Calculate the date one month ago
        $oneMonthAgo = Carbon::now()->subMonth();

        // Get count of notifications to be deleted
        $count = DatabaseNotification::where('created_at', '<', $oneMonthAgo)->count();

        if ($count === 0) {
            $this->info('No old notifications found to delete.');
            return Command::SUCCESS;
        }

        $deletedCount = DatabaseNotification::where('created_at', '<', $oneMonthAgo)->delete();

        $this->info("Successfully deleted {$deletedCount} notifications older than one month.");

        return Command::SUCCESS;
    }
}
