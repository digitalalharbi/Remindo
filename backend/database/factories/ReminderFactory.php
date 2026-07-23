<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Reminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'title' => fake()->words(3, true),
            'expiry_date' => fake()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'status' => 'active',
            'recurrence' => 'none',
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'expiry_date' => now()->subDays(3)->toDateString(),
        ]);
    }
}
