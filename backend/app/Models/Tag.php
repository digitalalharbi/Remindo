<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = ['organization_id', 'name'];

    public function reminders(): BelongsToMany
    {
        return $this->belongsToMany(Reminder::class, 'reminder_tag');
    }
}
