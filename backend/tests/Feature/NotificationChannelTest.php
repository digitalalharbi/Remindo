<?php

namespace Tests\Feature;

use App\Jobs\SendReminderNotification;
use App\Models\CreditPack;
use App\Models\NotificationPreference;
use App\Models\Reminder;
use App\Models\ReminderNotification;
use App\Notifications\ReminderDueNotification;
use App\Services\Notifications\CreditService;
use App\Support\Tenancy;
use Database\Seeders\PlatformSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\CreatesTenants;
use Tests\TestCase;

class NotificationChannelTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
        $this->seed(PlatformSeeder::class);
    }

    private function dueNotification($org, $user, string $channel): ReminderNotification
    {
        $reminder = Reminder::factory()->create([
            'organization_id' => $org->id,
            'created_by' => $user->id,
            'status' => 'active',
        ]);

        return ReminderNotification::create([
            'reminder_id' => $reminder->id,
            'offset_days' => 1,
            'channel' => $channel,
            'send_at' => now()->subMinute(),
            'status' => 'pending',
        ]);
    }

    public function test_sms_send_is_blocked_without_credits(): void
    {
        Notification::fake();
        [$user, $org] = $this->createUserWithOrganization();
        $n = $this->dueNotification($org, $user, 'sms');

        (new SendReminderNotification($n->id))->handle();

        $this->assertEquals('failed', $n->fresh()->status);
        $this->assertEquals('insufficient_credits', $n->fresh()->error);
        Notification::assertNothingSent();
    }

    public function test_sms_send_deducts_a_credit_when_available(): void
    {
        Notification::fake();
        [$user, $org] = $this->createUserWithOrganization();
        Tenancy::set($org->id);
        app(CreditService::class)->add($org, 'sms', 5);

        $n = $this->dueNotification($org, $user, 'sms');
        (new SendReminderNotification($n->id))->handle();

        $this->assertEquals(4, app(CreditService::class)->balance($org, 'sms'));
        Notification::assertSentTo($user, ReminderDueNotification::class);
    }

    public function test_quiet_hours_suppress_interruptive_channels(): void
    {
        Notification::fake();
        [$user, $org] = $this->createUserWithOrganization(['timezone' => 'UTC']);
        app(CreditService::class)->add($org, 'whatsapp', 5);

        // Quiet all day.
        NotificationPreference::create([
            'user_id' => $user->id,
            'quiet_hours_enabled' => true,
            'quiet_start' => 0,
            'quiet_end' => 23,
        ]);

        $n = $this->dueNotification($org, $user, 'whatsapp');
        (new SendReminderNotification($n->id))->handle();

        $this->assertEquals('quiet_hours', $n->fresh()->error);
        Notification::assertNothingSent();
    }

    public function test_disabled_channel_is_skipped(): void
    {
        Notification::fake();
        [$user, $org] = $this->createUserWithOrganization();
        NotificationPreference::create(['user_id' => $user->id, 'channels' => ['email']]);

        $n = $this->dueNotification($org, $user, 'in_app');
        (new SendReminderNotification($n->id))->handle();

        $this->assertEquals('channel_disabled', $n->fresh()->error);
    }

    public function test_a_user_can_buy_a_credit_pack(): void
    {
        [$user, $org] = $this->createUserWithOrganization();
        $pack = CreditPack::where('channel', 'sms')->first();

        $this->actingAs($user)->postJson('/api/channels/credits/buy', ['pack_id' => $pack->id])
            ->assertOk()
            ->assertJsonPath('data.channel', 'sms');

        $this->assertEquals($pack->credits, app(CreditService::class)->balance($org->fresh(), 'sms'));
    }

    public function test_a_user_can_create_a_webhook_and_get_its_secret_once(): void
    {
        [$user] = $this->createUserWithOrganization();

        $res = $this->actingAs($user)->postJson('/api/channels/webhooks', [
            'url' => 'https://example.com/hook',
            'events' => ['reminder.created'],
        ]);
        $res->assertCreated();
        $this->assertNotEmpty($res->json('data.secret'));

        // Secret is not returned again in the list.
        $this->actingAs($user)->getJson('/api/channels/webhooks')
            ->assertOk()
            ->assertJsonMissing(['secret' => $res->json('data.secret')]);
    }
}
