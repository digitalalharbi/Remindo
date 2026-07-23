<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasUuids;

    protected $fillable = ['group', 'key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        return static::where('group', $group)->where('key', $key)->value('value') ?? $default;
    }

    public static function put(string $group, string $key, mixed $value): void
    {
        static::updateOrCreate(['group' => $group, 'key' => $key], ['value' => $value]);
    }
}
