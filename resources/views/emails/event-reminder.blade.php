<h2>Event Reminder</h2>

<p>Hello {{ $user->name }},</p>

<p>This is a reminder that you are registered for:</p>

<h3>{{ $event->title }}</h3>

<p>
    Date:
    {{ $event->start_at->format('d M Y') }}
</p>

<p>
    Time:
    {{ $event->start_at->format('h:i A') }}
</p>

<p>
    Location:
    {{ $event->location }}
</p>

<p>
    We look forward to seeing you!
</p>