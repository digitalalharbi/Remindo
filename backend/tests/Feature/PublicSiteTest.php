<?php

namespace Tests\Feature;

use Database\Seeders\PlanSeeder;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->seed(PlatformSeeder::class);
    }

    public function test_public_can_read_published_faqs(): void
    {
        $this->getJson('/api/faqs')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'question', 'answer']]]);
    }

    public function test_public_can_read_plans(): void
    {
        $this->getJson('/api/plans')->assertOk()->assertJsonPath('data.0.key', 'free');
    }

    public function test_contact_form_stores_a_message(): void
    {
        $this->postJson('/api/contact', [
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'message' => 'Hello Remindo',
            'locale' => 'en',
        ])->assertOk();

        $this->assertDatabaseHas('contact_messages', ['email' => 'sara@example.com']);
    }

    public function test_contact_form_validates_input(): void
    {
        $this->postJson('/api/contact', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'message']);
    }
}
