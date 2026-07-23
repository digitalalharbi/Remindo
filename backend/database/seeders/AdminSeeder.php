<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /** A Remindo staff account for the Super Admin panel. */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@remindo.me'],
            [
                'name' => 'Remindo Admin',
                'password' => env('ADMIN_SEED_PASSWORD', 'AdminPass123!'),
                'locale' => 'en',
                'country' => 'SA',
                'timezone' => 'Asia/Riyadh',
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        if (! $admin->current_organization_id) {
            app(OrganizationService::class)->createPersonalOrganizationFor($admin);
        }
    }
}
