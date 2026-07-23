<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_reminder_can_be_created_in_one_request(): void
    {
        $response = $this->postJson('/api/v1/reminders', [
            'name' => 'Car insurance',
            'expires_at' => now()->addMonth()->toDateString(),
            'remind_days_before' => 7,
            'channel' => 'email',
            'category' => 'insurance',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Car insurance')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('reminders', ['name' => 'Car insurance']);
    }

    public function test_invalid_channels_are_rejected(): void
    {
        $this->postJson('/api/v1/reminders', [
            'name' => 'License',
            'expires_at' => now()->addMonth()->toDateString(),
            'remind_days_before' => 7,
            'channel' => 'whatsapp',
        ])->assertUnprocessable()->assertJsonValidationErrors('channel');
    }
}
