<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['code', 'name', 'price_monthly', 'currency', 'reminder_limit', 'email_limit', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
