<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasUuids;

    protected $fillable = ['key', 'description', 'enabled', 'meta'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'meta' => 'array'];
    }
}
