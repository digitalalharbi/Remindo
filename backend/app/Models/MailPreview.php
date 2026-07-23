<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * A captured copy of an outgoing email, shown in the preview environment's
 * admin mailbox. Only written when config('preview.capture_mail') is on.
 */
class MailPreview extends Model
{
    use HasUuids;

    protected $fillable = [
        'to', 'cc', 'subject', 'from', 'html', 'text',
    ];
}
