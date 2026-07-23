<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /** System categories + this organization's custom categories. */
    public function index(): JsonResponse
    {
        $categories = Category::query()->orderBy('is_system', 'desc')->get();

        return ApiResponse::success(CategoryResource::collection($categories));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'color' => ['sometimes', 'string', 'max:9'],
            'icon' => ['nullable', 'string', 'max:48'],
        ]);

        $category = Category::create([
            'name' => ['en' => $data['name'], 'ar' => $data['name'], 'es' => $data['name'], 'tr' => $data['name']],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'color' => $data['color'] ?? '#4F6BED',
            'icon' => $data['icon'] ?? null,
            'is_system' => false,
        ]);

        return ApiResponse::success(new CategoryResource($category), status: 201);
    }

    public function destroy(Category $category): JsonResponse
    {
        abort_if($category->is_system, 403);
        $category->delete();

        return ApiResponse::message(__('reminders.deleted'));
    }
}
