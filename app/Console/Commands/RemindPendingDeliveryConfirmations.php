<?php

namespace App\Console\Commands;

use App\Models\DeliveryConfirmation;
use App\Notifications\DeliveryConfirmationReminderNotification;
use Illuminate\Console\Command;

class RemindPendingDeliveryConfirmations extends Command
{
    // اسم الأمر: php artisan delivery-confirmations:remind
    protected $signature = 'delivery-confirmations:remind {--days=3 : عدد الأيام قبل إرسال التذكير}';

    protected $description = 'Send an email reminder to recipients who left their delivery confirmation pending for N days';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $confirmations = DeliveryConfirmation::query()
            ->where('status', 'pending')
            ->whereNull('reminder_sent_at')
            ->where('created_at', '<=', now()->subDays($days))
            ->with('recipient')
            ->get();

        if ($confirmations->isEmpty()) {
            $this->info('No pending confirmations older than ' . $days . ' day(s).');
            return self::SUCCESS;
        }

        foreach ($confirmations as $confirmation) {
            if (!$confirmation->recipient) {
                continue;
            }

            $confirmation->recipient->notify(
                new DeliveryConfirmationReminderNotification($confirmation)
            );

            $confirmation->update(['reminder_sent_at' => now()]);

            $this->line('Reminder sent for confirmation #' . $confirmation->id);
        }

        $this->info($confirmations->count() . ' reminder(s) sent.');

        return self::SUCCESS;
    }
}