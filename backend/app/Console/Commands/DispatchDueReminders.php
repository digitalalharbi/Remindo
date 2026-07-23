<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderNotification;
use App\Models\ReminderNotification;
use Illuminate\Console\Command;

class DispatchDueReminders extends Command
{
    protected $signature = 'reminders:dispatch-due';

    protected $description = 'Queue a job for every reminder notification whose send time has arrived.';

    public function handle(): int
    {
        $due = ReminderNotification::query()
            ->where('status', 'pending')
            ->where('send_at', '<=', now())
            ->limit(500)
            ->get();

        foreach ($due as $notification) {
            // Optimistic lock: only dispatch if we transition pending → queued.
            $claimed = ReminderNotification::where('id', $notification->id)
                ->where('status', 'pending')
                ->update(['status' => 'sent', 'sent_at' => now()]);

            if ($claimed) {
                SendReminderNotification::dispatch($notification->id);
            }
        }

        $this->info("Dispatched {$due->count()} due reminder notification(s).");

        return self::SUCCESS;
    }
}
