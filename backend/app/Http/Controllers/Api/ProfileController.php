<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'country' => ['required', 'string', 'size:2'],
            'timezone' => ['required', 'timezone:all'],
            'locale' => ['required', 'in:ar,en'],
            'currency' => ['required', 'in:SAR,USD,EUR,TRY'],
        ]);
        $request->user()->update($data);

        return response()->json(['data' => $request->user()->fresh()]);
    }
}
