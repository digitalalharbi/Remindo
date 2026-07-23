<?php

namespace Tests\Feature;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CreatesTenants;
use Tests\TestCase;

class AiExtractionTest extends TestCase
{
    use CreatesTenants, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    private function onPaidPlan(): array
    {
        [$user, $org] = $this->createUserWithOrganization();
        $org->update(['plan_id' => Plan::where('key', 'personal')->value('id')]);

        return [$user, $org->fresh('plan')];
    }

    public function test_uploading_and_extracting_returns_fields_for_review(): void
    {
        Storage::fake('local');
        [$user] = $this->onPaidPlan();

        $upload = $this->actingAs($user)->postJson('/api/documents', [
            'file' => UploadedFile::fake()->create('car-insurance.pdf', 200, 'application/pdf'),
        ]);
        $upload->assertCreated();
        $documentId = $upload->json('data.id');

        $res = $this->actingAs($user)->postJson("/api/documents/{$documentId}/extract");

        $res->assertOk()
            ->assertJsonPath('data.requires_review', true)
            ->assertJsonPath('data.provider', 'mock');

        $this->assertNotNull($res->json('data.expiry_date'));
        // Insurance → yearly offsets suggested.
        $this->assertEquals([30, 7, 1], $res->json('data.suggested_offsets'));
    }

    public function test_extraction_is_blocked_on_a_plan_without_ai_allowance(): void
    {
        Storage::fake('local');
        // Default personal-org is on the free plan (ai_operations_limit = 0).
        [$user] = $this->createUserWithOrganization();

        $upload = $this->actingAs($user)->postJson('/api/documents', [
            'file' => UploadedFile::fake()->create('lease.pdf', 100, 'application/pdf'),
        ]);
        $documentId = $upload->json('data.id');

        $this->actingAs($user)
            ->postJson("/api/documents/{$documentId}/extract")
            ->assertStatus(402);
    }

    public function test_rejects_disallowed_file_types(): void
    {
        Storage::fake('local');
        [$user] = $this->onPaidPlan();

        $this->actingAs($user)->postJson('/api/documents', [
            'file' => UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload'),
        ])->assertStatus(422)->assertJsonValidationErrors('file');
    }

    public function test_natural_language_parsing_extracts_offsets(): void
    {
        [$user] = $this->onPaidPlan();

        $res = $this->actingAs($user)->postJson('/api/ai/parse', [
            'text' => 'ذكّرني بتجديد التأمين قبل شهر وأسبوع',
        ]);

        $res->assertOk()->assertJsonPath('data.requires_review', true);
        // "شهر" → 30, "أسبوع" → 7, sorted desc.
        $this->assertEquals([30, 7], $res->json('data.suggested_offsets'));
    }
}
