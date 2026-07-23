<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasUuids;

    protected $fillable = ['code', 'name', 'english_name', 'rtl', 'enabled', 'is_default', 'sort_order'];

    protected function casts(): array
    {
        return ['rtl' => 'boolean', 'enabled' => 'boolean', 'is_default' => 'boolean'];
    }
}
