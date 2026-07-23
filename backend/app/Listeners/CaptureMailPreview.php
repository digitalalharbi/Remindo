<?php

namespace App\Listeners;

use App\Models\MailPreview;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Email;

/**
 * Captures every outgoing email into the `mail_previews` table so the preview
 * environment has an online mailbox (Admin → Mail log) without depending on an
 * external inbox. Inert unless config('preview.capture_mail') is enabled.
 */
class CaptureMailPreview
{
    public function handle(MessageSending $event): void
    {
        if (! config('preview.capture_mail')) {
            return;
        }

        /** @var Email $message */
        $message = $event->message;

        $addresses = fn (array $list) => implode(', ', array_map(
            fn ($a) => trim($a->getName().' <'.$a->getAddress().'>'),
            $list,
        ));

        $html = $message->getHtmlBody();
        $text = $message->getTextBody();

        MailPreview::create([
            'to' => $addresses($message->getTo()),
            'cc' => $addresses($message->getCc()),
            'from' => $addresses($message->getFrom()),
            'subject' => $message->getSubject(),
            'html' => is_string($html) ? $html : null,
            'text' => is_string($text) ? $text : null,
        ]);
    }
}
