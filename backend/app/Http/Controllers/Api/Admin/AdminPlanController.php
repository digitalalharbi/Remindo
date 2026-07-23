<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\ActivityLogger;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPlanController extends Controller
{
    public function __construct(private readonly ActivityLogger $audit) {}

    private function rules(bool $creating): array
    {
        return [
            'key' => [$creating ? 'required' : 'sometimes', 'string', 'max:40', 'alpha_dash'],
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string'],
            'description' => ['nullable', 'array'],
            'price_monthly' => ['required', 'integer', 'min:0'],
            'price_yearly' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'reminder_limit' => ['required', 'integer', 'min:-1'],
            'user_limit' => ['required', 'integer', 'min:1'],
            'ai_operations_limit' => ['required', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            // Localized/multi-currency prices
            'prices' => ['sometimes', 'array'],
            'prices.*.currency' => ['required', 'string', 'size:3'],
            'prices.*.price_monthly' => ['required', 'integer', 'min:0'],
            'prices.*.price_yearly' => ['required', 'integer', 'min:0'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(true) + [
            'key' => ['required', 'string', 'max:40', 'alpha_dash', Rule::unique('plans', 'key')],
        ]);

        $plan = Plan::create($data + ['sort_order' => Plan::max('sort_order') + 1]);
        $this->syncPrices($plan, $data['prices'] ?? []);
        $this->audit->logAdmin('plan.created', $plan, ['key' => $plan->key]);

        return ApiResponse::success($plan->load('prices'), status: 201);
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $data = $request->validate($this->rules(false));
        $plan->update($data);
        if ($request->has('prices')) {
            $this->syncPrices($plan, $data['prices'] ?? []);
        }
        $this->audit->logAdmin('plan.updated', $plan, ['key' => $plan->key]);

        return ApiResponse::success($plan->fresh('prices'));
    }

    public function toggle(Plan $plan): JsonResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);
        $this->audit->logAdmin('plan.toggled', $plan, ['is_active' => $plan->is_active]);

        return ApiResponse::success($plan);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['uuid', Rule::exists('plans', 'id')],
        ]);

        foreach ($data['order'] as $index => $id) {
            Plan::where('id', $id)->update(['sort_order' => $index]);
        }
        $this->audit->logAdmin('plan.reordered');

        return ApiResponse::message('ok');
    }

    public function destroy(Plan $plan): JsonResponse
    {
        // Never delete a plan that has active subscribers — deactivate instead.
        if ($plan->key === 'free' || Subscription::where('plan_id', $plan->id)->where('status', 'active')->exists()) {
            return ApiResponse::error(__('admin.plan_in_use'), 422);
        }

        $this->audit->logAdmin('plan.deleted', $plan, ['key' => $plan->key]);
        $plan->delete();

        return ApiResponse::message('ok');
    }

    private function syncPrices(Plan $plan, array $prices): void
    {
        $plan->prices()->delete();
        foreach ($prices as $p) {
            $plan->prices()->create($p);
        }
    }
}
