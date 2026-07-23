<?php

namespace App\Services\Calendar;

use App\Models\Reminder;
use Illuminate\Support\Collection;

/**
 * Generates RFC 5545 iCalendar (.ics) output. Fully functional and provider-free
 * — this is the reliable "export reminders to calendar" path that works without
 * any third-party credentials.
 */
class IcsGenerator
{
    /** Build a VCALENDAR string from one or more reminders. */
    public function forReminders(Collection|array $reminders, string $calendarName = 'Remindo'): string
    {
        $reminders = collect($reminders);

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Remindo//Reminders//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:'.$this->escape($calendarName),
        ];

        foreach ($reminders as $reminder) {
            $lines = array_merge($lines, $this->event($reminder));
        }

        $lines[] = 'END:VCALENDAR';

        // RFC 5545 requires CRLF line endings.
        return implode("\r\n", $lines)."\r\n";
    }

    private function event(Reminder $reminder): array
    {
        $date = $reminder->expiry_date->format('Ymd');
        $stamp = now()->utc()->format('Ymd\THis\Z');
        $uid = $reminder->id.'@remindo.me';

        $event = [
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$stamp,
            // All-day event on the expiry date.
            'DTSTART;VALUE=DATE:'.$date,
            'DTEND;VALUE=DATE:'.$reminder->expiry_date->copy()->addDay()->format('Ymd'),
            'SUMMARY:'.$this->escape($reminder->title),
        ];

        if ($reminder->description) {
            $event[] = 'DESCRIPTION:'.$this->escape($reminder->description);
        }

        // A VALARM one day before, mirroring the reminder intent.
        $event[] = 'BEGIN:VALARM';
        $event[] = 'TRIGGER:-P1D';
        $event[] = 'ACTION:DISPLAY';
        $event[] = 'DESCRIPTION:'.$this->escape($reminder->title);
        $event[] = 'END:VALARM';

        $event[] = 'END:VEVENT';

        return $event;
    }

    private function escape(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\n"],
            ['\\\\', '\\;', '\\,', '\\n'],
            $value,
        );
    }
}
