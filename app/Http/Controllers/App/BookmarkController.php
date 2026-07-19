<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\Bookmark\DestroyRequest;
use App\Http\Requests\App\Bookmark\IndexRequest;
use App\Http\Requests\App\Bookmark\StoreRequest;
use App\Http\Requests\App\Bookmark\UpdateRequest;
use App\Http\Resources\App\BookmarkResource;
use App\Models\Bookmark;
use Illuminate\Http\JsonResponse;

class BookmarkController extends Controller
{
    /**
     * List the authenticated user's bookmarks with optional category filter.
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $bookmarks = $request->user()->bookmarks()
            ->with('tags')
            ->forFavorited($request->validated('favorited'))
            ->forCategory($request->validated('category_id'))
            ->sortBy($request->validated('sort_by'), $request->validated('sort_dir'))
            ->paginate($request->validated('per_page', 15));

        return response()->json(BookmarkResource::paginated($bookmarks));
    }

    /**
     * Create a new bookmark.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $bookmark = $request->user()->bookmarks()->create($request->validated());
        $bookmark->syncTags($request->validated('tags'));

        return response()->json([
            'data' => new BookmarkResource($bookmark->load('tags')),
        ], 201);
    }

    /**
     * Update a bookmark.
     */
    public function update(UpdateRequest $request, Bookmark $bookmark): JsonResponse
    {
        $bookmark->update($request->validated());
        $bookmark->syncTags($request->validated('tags'));

        return response()->json([
            'data' => new BookmarkResource($bookmark->load('tags')),
        ]);
    }

    /**
     * Delete a bookmark.
     */
    public function destroy(DestroyRequest $request, Bookmark $bookmark): JsonResponse
    {
        $bookmark->delete();

        return response()->json(null, 204);
    }
}

