<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserTag\DestroyRequest;
use App\Http\Requests\Admin\UserTag\IndexRequest;
use App\Http\Resources\Admin\TagResource;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserTagController extends Controller
{
    /**
     * List a user's tags.
     */
    public function index(IndexRequest $request, User $user): JsonResponse
    {
        $tags = $user->tags()->sortBy()->get();

        return response()->json([
            'data' => TagResource::collection($tags),
        ]);
    }

    /**
     * Delete a user's tag.
     *
     * Associated bookmark_tag pivot rows are removed via ON DELETE CASCADE.
     */
    public function destroy(DestroyRequest $request, User $user, Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(null, 204);
    }
}
