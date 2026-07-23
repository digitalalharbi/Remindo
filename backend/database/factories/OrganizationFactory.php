<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'type' => 'personal',
            'owner_id' => User::factory(),
            'plan_id' => fn () => Plan::where('key', 'free')->value('id'),
            'country' => 'SA',
            'timezone' => 'Asia/Riyadh',
            'currency' => 'SAR',
        ];
    }
}
