<?php

namespace App\Console\Commands;

use App\Events\EventReminder;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Send reminders for upcoming events';
    /**
     * Execute the console command.
     */
    public function handle()
    {
         $registrations = EventRegistration::with([
                'event',
                'user',
            ])
            ->whereHas('event', function ($query) {
                $query->whereDate(
                    'start_at',
                    now()->addDays(2)
                );
            })
            ->where('reminder_sent', false)
            ->get();

            $events = $registrations->pluck('event')->unique('id');

            foreach ($events as $event) {
            EventReminder::dispatch($event);
     }

            // Mark all fetched registrations as reminded
            EventRegistration::whereIn('id', $registrations->pluck('id'))
                ->update(['reminder_sent' => true]);

        

        $this->info(
            $registrations->count() . ' event reminder(s) dispatched.'
        );
    
    }
}
