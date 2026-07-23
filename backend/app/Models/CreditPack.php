<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CreditPack extends Model
{
    use HasUuids;

    protected $fillable = ['channel', 'name', 'credits', 'price', 'currency', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
