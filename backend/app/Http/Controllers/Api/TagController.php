<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Support\ApiResponse;
use App\Support\Tenancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(TagResource::collection(Tag::orderBy('name')->get()));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:60']]);

        $tag = Tag::firstOrCreate([
            'organization_id' => Tenancy::currentId(),
            'name' => $data['name'],
        ]);

        return ApiResponse::success(new TagResource($tag), status: 201);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return ApiResponse::message(__('reminders.deleted'));
    }
}
