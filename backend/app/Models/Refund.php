<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasUuids;

    protected $fillable = ['invoice_id', 'organization_id', 'amount', 'currency', 'reason', 'provider_reference'];
}
