<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserCategory\DestroyRequest;
use App\Http\Requests\Admin\UserCategory\IndexRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserCategoryController extends Controller
{
    /**
     * List a user's categories.
     */
    public function index(IndexRequest $request, User $user): JsonResponse
    {
        $categories = $user->categories()
            ->sortBy($request->validated('sort_by'), $request->validated('sort_dir'))
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Delete a user's category.
     */
    public function destroy(DestroyRequest $request, User $user, Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(null, 204);
    }
}
