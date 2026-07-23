<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    /** Categories also expose shared system rows (organization_id = null). */
    public bool $includesSystemRecords = true;

    protected $fillable = ['organization_id', 'name', 'slug', 'color', 'icon', 'is_system'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }
}
