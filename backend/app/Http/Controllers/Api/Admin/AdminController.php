<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Reminder;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payments\BillingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Super Admin API. All queries run WITHOUT the tenant scope — staff see the
 * whole platform, not a single organization.
 */
class AdminController extends Controller
{
    /** Platform overview counters. */
    public function stats(): JsonResponse
    {
        $paidInvoices = Invoice::where('status', 'paid');

        return ApiResponse::success([
            'users' => User::count(),
            'organizations' => Organization::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'reminders' => Reminder::withoutGlobalScope('organization')->count(),
            'revenue_total' => (int) (clone $paidInvoices)->sum('total'),
            'revenue_currency' => 'SAR',
            'ai_operations' => DB::table('ai_usages')->count(),
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $query = User::query()->withCount('ownedOrganizations');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(fn ($q) => $q->where('name', 'ilike', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%"));
        }

        $users = $query->latest()->paginate(20);

        return ApiResponse::success(
            collect($users->items())->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'locale' => $u->locale,
                'country' => $u->country,
                'is_super_admin' => (bool) $u->is_super_admin,
                'verified' => ! is_null($u->email_verified_at),
                'suspended' => ! is_null($u->suspended_at),
                'created_at' => $u->created_at?->toDateString(),
            ]),
            meta: $this->pageMeta($users),
        );
    }

    public function organizations(): JsonResponse
    {
        $orgs = Organization::with('plan')->withCount('members')->latest()->paginate(20);

        return ApiResponse::success(
            collect($orgs->items())->map(fn (Organization $o) => [
                'id' => $o->id,
                'name' => $o->name,
                'type' => $o->type,
                'currency' => $o->currency,
                'plan' => $o->plan?->key,
                'members' => $o->members_count,
                'suspended' => ! is_null($o->suspended_at),
                'created_at' => $o->created_at?->toDateString(),
            ]),
            meta: $this->pageMeta($orgs),
        );
    }

    public function plans(): JsonResponse
    {
        return ApiResponse::success(
            Plan::orderBy('sort_order')->get()->map(fn (Plan $p) => [
                'id' => $p->id,
                'key' => $p->key,
                'name' => $p->name,
                'price_monthly' => $p->price_monthly,
                'price_yearly' => $p->price_yearly,
                'currency' => $p->currency,
                'reminder_limit' => $p->reminder_limit,
                'user_limit' => $p->user_limit,
                'ai_operations_limit' => $p->ai_operations_limit,
                'is_active' => $p->is_active,
            ]),
        );
    }

    public function invoices(): JsonResponse
    {
        $invoices = Invoice::with('organization')->latest('issued_at')->paginate(20);

        return ApiResponse::success(
            collect($invoices->items())->map(fn (Invoice $i) => [
                'id' => $i->id,
                'number' => $i->number,
                'organization' => $i->organization?->name,
                'total' => $i->total,
                'currency' => $i->currency,
                'status' => $i->status,
                'provider' => $i->provider,
                'issued_at' => $i->issued_at?->toDateString(),
            ]),
            meta: $this->pageMeta($invoices),
        );
    }

    /** Record a refund against an invoice. */
    public function refund(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['nullable', 'integer', 'min:1', 'max:'.$invoice->total],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $refund = app(BillingService::class)
            ->refund($invoice, $data['amount'] ?? null, $data['reason'] ?? null);

        return ApiResponse::success($refund);
    }

    public function auditLogs(): JsonResponse
    {
        $logs = ActivityLog::with('user')->latest()->paginate(30);

        return ApiResponse::success(
            collect($logs->items())->map(fn (ActivityLog $l) => [
                'id' => $l->id,
                'action' => $l->action,
                'user' => $l->user?->name,
                'subject_type' => class_basename((string) $l->subject_type),
                'properties' => $l->properties,
                'created_at' => $l->created_at?->toIso8601String(),
            ]),
            meta: $this->pageMeta($logs),
        );
    }

    private function pageMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }
}
