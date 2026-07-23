<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    use HasUuids;

    protected $fillable = [
        'webhook_endpoint_id', 'event', 'payload', 'status', 'response_code', 'attempts', 'delivered_at',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array', 'delivered_at' => 'datetime'];
    }
}
