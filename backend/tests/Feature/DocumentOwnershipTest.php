<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CreatesTenants;
use Tests\TestCase;

class DocumentOwnershipTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_a_user_cannot_access_another_organizations_document(): void
    {
        Storage::fake('local');

        [, $aliceOrg] = $this->createUserWithOrganization(['email' => 'alice-doc@example.com']);
        $doc = Tenancy::forOrganization($aliceOrg->id, fn () => Document::create([
            'organization_id' => $aliceOrg->id,
            'disk' => 'local',
            'path' => 'documents/x.pdf',
            'original_name' => 'x.pdf',
            'mime' => 'application/pdf',
            'size' => 100,
        ]));

        [$bob] = $this->createUserWithOrganization(['email' => 'bob-doc@example.com']);

        // The tenant scope hides it → 404, and delete is likewise blocked.
        $this->actingAs($bob)->getJson("/api/documents/{$doc->id}/download")->assertNotFound();
        $this->actingAs($bob)->deleteJson("/api/documents/{$doc->id}")->assertNotFound();
    }

    public function test_a_user_can_delete_their_own_document(): void
    {
        Storage::fake('local');
        [$user] = $this->createUserWithOrganization();

        $upload = $this->actingAs($user)->postJson('/api/documents', [
            'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]);
        $id = $upload->json('data.id');

        $this->actingAs($user)->deleteJson("/api/documents/{$id}")->assertOk();
        $this->assertDatabaseMissing('documents', ['id' => $id]);
    }

    public function test_ai_is_not_required_to_create_a_reminder_manually(): void
    {
        // A free-plan user (no AI allowance) can still create reminders manually.
        [$user] = $this->createUserWithOrganization();

        $this->actingAs($user)->postJson('/api/reminders', [
            'title' => 'Manual reminder',
            'expiry_date' => now()->addDays(20)->toDateString(),
        ])->assertCreated();
    }
}
