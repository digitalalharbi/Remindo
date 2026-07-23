<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id', 'subscription_id', 'number', 'status',
        'subtotal', 'tax', 'total', 'currency', 'provider', 'provider_reference',
        'lines', 'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'lines' => 'array',
            'issued_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
