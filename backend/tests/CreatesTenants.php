<?php

namespace Tests;

use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationService;
use App\Support\Tenancy;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PlanSeeder;

trait CreatesTenants
{
    protected function seedBaseData(): void
    {
        $this->seed(PlanSeeder::class);
        $this->seed(CategorySeeder::class);
    }

    /** Create a user with a personal organization and set it as the active tenant. */
    protected function createUserWithOrganization(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $organization = app(OrganizationService::class)->createPersonalOrganizationFor($user);
        Tenancy::set($organization->id);

        return [$user->fresh(), $organization];
    }
}
