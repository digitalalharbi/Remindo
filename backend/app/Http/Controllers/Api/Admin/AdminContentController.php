<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditPack;
use App\Models\Faq;
use App\Models\FeatureFlag;
use App\Models\Language;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Admin management of feature flags, languages, FAQs, and generic site content
 * (SEO defaults, email/notification template overrides).
 */
class AdminContentController extends Controller
{
    public function __construct(private readonly ActivityLogger $audit) {}

    /* ── Feature flags ── */

    public function flags(): JsonResponse
    {
        return ApiResponse::success(FeatureFlag::orderBy('key')->get());
    }

    public function upsertFlag(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:60', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:255'],
            'enabled' => ['boolean'],
        ]);

        $flag = FeatureFlag::updateOrCreate(['key' => $data['key']], $data);
        $this->audit->logAdmin('flag.updated', $flag, ['key' => $flag->key, 'enabled' => $flag->enabled]);

        return ApiResponse::success($flag);
    }

    /* ── Languages ── */

    public function languages(): JsonResponse
    {
        return ApiResponse::success(Language::orderBy('sort_order')->get());
    }

    public function toggleLanguage(Request $request, Language $language): JsonResponse
    {
        // Never disable the default language or leave zero enabled.
        if ($language->is_default) {
            return ApiResponse::error(__('admin.cannot_disable_default_language'), 422);
        }
        $language->update(['enabled' => ! $language->enabled]);
        $this->audit->logAdmin('language.toggled', $language, ['code' => $language->code, 'enabled' => $language->enabled]);

        return ApiResponse::success($language);
    }

    /* ── FAQs ── */

    public function faqs(): JsonResponse
    {
        return ApiResponse::success(Faq::orderBy('sort_order')->get());
    }

    public function storeFaq(Request $request): JsonResponse
    {
        $data = $this->faqData($request);
        $faq = Faq::create($data + ['sort_order' => Faq::max('sort_order') + 1]);
        $this->audit->logAdmin('faq.created', $faq);

        return ApiResponse::success($faq, status: 201);
    }

    public function updateFaq(Request $request, Faq $faq): JsonResponse
    {
        $faq->update($this->faqData($request));
        $this->audit->logAdmin('faq.updated', $faq);

        return ApiResponse::success($faq);
    }

    public function destroyFaq(Faq $faq): JsonResponse
    {
        $this->audit->logAdmin('faq.deleted', $faq);
        $faq->delete();

        return ApiResponse::message('ok');
    }

    private function faqData(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'array'],
            'question.en' => ['required', 'string'],
            'answer' => ['required', 'array'],
            'answer.en' => ['required', 'string'],
            'category' => ['sometimes', 'string', 'max:60'],
            'published' => ['boolean'],
        ]);
    }

    /* ── Credit packs + provider pricing ── */

    public function creditPacks(): JsonResponse
    {
        return ApiResponse::success(CreditPack::orderBy('channel')->get());
    }

    public function saveCreditPack(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'uuid'],
            'channel' => ['required', Rule::in(['sms', 'whatsapp', 'ai'])],
            'name' => ['required', 'string', 'max:80'],
            'credits' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['boolean'],
        ]);

        $pack = CreditPack::updateOrCreate(
            ['id' => $data['id'] ?? (string) Str::uuid7()],
            collect($data)->except('id')->all(),
        );
        $this->audit->logAdmin('credit_pack.saved', $pack, ['channel' => $pack->channel]);

        return ApiResponse::success($pack);
    }

    public function deleteCreditPack(CreditPack $creditPack): JsonResponse
    {
        $this->audit->logAdmin('credit_pack.deleted', $creditPack);
        $creditPack->delete();

        return ApiResponse::message('ok');
    }

    /* ── Generic settings (SEO / content / templates) ── */

    public function settings(Request $request): JsonResponse
    {
        $group = $request->validate(['group' => ['required', Rule::in(['seo', 'content', 'email', 'notification'])]])['group'];

        return ApiResponse::success(Setting::where('group', $group)->get(['group', 'key', 'value']));
    }

    public function saveSetting(Request $request): JsonResponse
    {
        $data = $request->validate([
            'group' => ['required', Rule::in(['seo', 'content', 'email', 'notification'])],
            'key' => ['required', 'string', 'max:120'],
            'value' => ['present'],
        ]);

        Setting::put($data['group'], $data['key'], $data['value']);
        $this->audit->logAdmin('setting.updated', null, ['group' => $data['group'], 'key' => $data['key']]);

        return ApiResponse::message('ok');
    }
}
