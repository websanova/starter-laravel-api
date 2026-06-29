<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Tag\DestroyRequest;
use App\Http\Requests\App\Tag\IndexRequest;
use App\Http\Requests\App\Tag\StoreRequest;
use App\Http\Requests\App\Tag\UpdateRequest;
use App\Http\Resources\App\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * List the authenticated user's tags.
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $tags = $request->user()->tags()->sortBy()->get();

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
     *
     * Associated bookmark_tag pivot rows are removed via ON DELETE CASCADE.
     */
    public function destroy(DestroyRequest $request, Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(null, 204);
    }
}

