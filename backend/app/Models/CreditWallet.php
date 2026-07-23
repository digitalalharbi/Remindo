<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CreditWallet extends Model
{
    use HasUuids;

    protected $fillable = ['organization_id', 'channel', 'balance'];

    public function transactions()
    {
        return $this->hasMany(CreditTransaction::class, 'wallet_id');
    }
}
