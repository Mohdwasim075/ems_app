<?php

namespace App\Listeners;

use App\Events\EventReminder;
use App\Mail\EventReminderMail;
use App\Models\Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendEventReminder implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventReminder $event): void
    {
        $eventModel = $event->event;

  

        foreach($eventModel->registrations as $registration){
            $user = $registration->user;

            Mail::to($user->email)
                    ->send(new EventReminderMail($eventModel, $user));

        }
    }
}
