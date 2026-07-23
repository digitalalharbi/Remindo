<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminder extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'organization_id', 'created_by', 'assigned_to', 'category_id', 'document_id',
        'title', 'description', 'reference_number', 'issuer',
        'expiry_date', 'issue_date', 'status',
        'recurrence', 'recurrence_interval', 'recurrence_unit',
        'completed_at', 'snoozed_until', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'issue_date' => 'date',
            'completed_at' => 'datetime',
            'snoozed_until' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'reminder_tag');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ReminderNotification::class);
    }

    /** Days until expiry (negative if overdue). */
    public function daysUntilExpiry(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'active')->whereDate('expiry_date', '<', now());
    }

    public function scopeUpcoming(Builder $query, int $days = 30): Builder
    {
        return $query->where('status', 'active')
            ->whereDate('expiry_date', '>=', now())
            ->whereDate('expiry_date', '<=', now()->addDays($days));
    }
}
