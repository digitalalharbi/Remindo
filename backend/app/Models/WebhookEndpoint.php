<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    use HasUuids;

    protected $fillable = ['organization_id', 'url', 'secret', 'events', 'is_active'];

    protected $hidden = ['secret'];

    protected function casts(): array
    {
        return ['secret' => 'encrypted', 'events' => 'array', 'is_active' => 'boolean'];
    }

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function subscribesTo(string $event): bool
    {
        return $this->is_active && ($this->events === null || in_array($event, $this->events, true));
    }
}
