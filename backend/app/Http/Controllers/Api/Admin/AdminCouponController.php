<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\ActivityLogger;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCouponController extends Controller
{
    public function __construct(private readonly ActivityLogger $audit) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(Coupon::latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'alpha_dash', Rule::unique('coupons', 'code')],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'integer', 'min:1'],
            'currency' => ['nullable', 'required_if:type,fixed', 'string', 'size:3'],
            'duration' => ['required', Rule::in(['once', 'repeating', 'forever'])],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $coupon = Coupon::create(['code' => strtoupper($data['code'])] + $data);
        $this->audit->logAdmin('coupon.created', $coupon, ['code' => $coupon->code]);

        return ApiResponse::success($coupon, status: 201);
    }

    public function update(Request $request, Coupon $coupon): JsonResponse
    {
        $data = $request->validate([
            'value' => ['sometimes', 'integer', 'min:1'],
            'max_redemptions' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);
        $coupon->update($data);
        $this->audit->logAdmin('coupon.updated', $coupon, ['code' => $coupon->code]);

        return ApiResponse::success($coupon);
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        $this->audit->logAdmin('coupon.deleted', $coupon, ['code' => $coupon->code]);
        $coupon->delete();

        return ApiResponse::message('ok');
    }
}
