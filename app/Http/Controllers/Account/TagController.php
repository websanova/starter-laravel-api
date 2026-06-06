<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Tag\DestroyRequest;
use App\Http\Requests\Account\Tag\IndexRequest;
use App\Http\Requests\Account\Tag\StoreRequest;
use App\Http\Requests\Account\Tag\UpdateRequest;
use App\Http\Resources\Account\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * List the authenticated user's tags.
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $tags = $request->user()->tags()->orderBy('name')->get();

        return response()->json([
            'data' => TagResource::collection($tags),
        ]);
    }

    /**
     * Create a new tag.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $tag = $request->user()->tags()->create($request->validated());

        return response()->json([
            'data' => new TagResource($tag),
        ], 201);
    }

    /**
     * Update a tag.
     */
    public function update(UpdateRequest $request, Tag $tag): JsonResponse
    {
        $tag->update($request->validated());

        return response()->json([
            'data' => new TagResource($tag),
        ]);
    }

    /**
     * Delete a tag.
     */
    public function destroy(DestroyRequest $request, Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(null, 204);
    }
}
