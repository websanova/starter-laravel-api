<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Category\DestroyRequest;
use App\Http\Requests\Account\Category\IndexRequest;
use App\Http\Requests\Account\Category\StoreRequest;
use App\Http\Requests\Account\Category\UpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * List the authenticated user's categories.
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $categories = $request->user()->categories()->latest()->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Create a new category.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $category = $request->user()->categories()->create($request->validated());

        return response()->json([
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Update a category.
     */
    public function update(UpdateRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json([
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Delete a category.
     */
    public function destroy(DestroyRequest $request, Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(null, 204);
    }
}
