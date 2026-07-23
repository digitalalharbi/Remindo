<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;

class OrganizationService
{
    /**
     * Create the default personal organization for a freshly registered user,
     * attach them as owner, put them on the free plan, and make it current.
     */
    public function createPersonalOrganizationFor(User $user): Organization
    {
        $organization = Organization::create([
            'name' => $user->name,
            'slug' => $this->uniqueSlug($user->name),
            'type' => 'personal',
            'owner_id' => $user->id,
            'plan_id' => Plan::where('key', 'free')->value('id'),
            'country' => $user->country,
            'timezone' => $user->timezone ?? 'UTC',
            'currency' => $this->currencyForCountry($user->country),
        ]);

        $organization->members()->attach($user->id, ['role' => 'owner']);

        $user->forceFill(['current_organization_id' => $organization->id])->save();

        return $organization;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'workspace';
        $slug = $base;
        $i = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function currencyForCountry(?string $country): string
    {
        return match ($country) {
            'SA', 'BH', 'KW', 'OM', 'QA', 'AE' => 'SAR',
            'TR' => 'TRY',
            'ES', 'FR', 'DE', 'IT' => 'EUR',
            'GB' => 'GBP',
            default => 'USD',
        };
    }
}
