<?php

namespace App\Models\Concerns;

use App\Models\Organization;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scopes a model to the "current" organization resolved from the authenticated
 * request. Guarantees tenant isolation at the query layer: models using this
 * trait can never leak rows across organizations unless the scope is explicitly
 * removed (e.g. in super-admin contexts).
 */
trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $builder) {
            if ($organizationId = Tenancy::currentId()) {
                $model = $builder->getModel();
                $column = $model->getTable().'.organization_id';

                // Some models (e.g. categories) also expose shared "system" rows
                // where organization_id is null. Those stay visible to every tenant.
                if (property_exists($model, 'includesSystemRecords') && $model->includesSystemRecords) {
                    $builder->where(function (Builder $q) use ($column, $organizationId) {
                        $q->where($column, $organizationId)->orWhereNull($column);
                    });
                } else {
                    $builder->where($column, $organizationId);
                }
            }
        });

        static::creating(function ($model) {
            if (empty($model->organization_id) && ($organizationId = Tenancy::currentId())) {
                $model->organization_id = $organizationId;
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** Bypass the tenant scope (super-admin / system jobs only). */
    public function scopeWithoutOrganizationScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('organization');
    }
}
