<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\Bookmark\DestroyRequest;
use App\Http\Requests\Account\Bookmark\IndexRequest;
use App\Http\Requests\Account\Bookmark\StoreRequest;
use App\Http\Requests\Account\Bookmark\UpdateRequest;
use App\Http\Resources\Account\BookmarkResource;
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
            ->forCategory($request->validated('category_id'))
            ->latest()
            ->paginate($request->validated('per_page', 15));

        return response()->json(BookmarkResource::collection($bookmarks)->response()->getData(true));
    }

    /**
     * Create a new bookmark.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $bookmark = $request->user()->bookmarks()->create($request->validated());

        return response()->json([
            'data' => new BookmarkResource($bookmark),
        ], 201);
    }

    /**
     * Update a bookmark.
     */
    public function update(UpdateRequest $request, Bookmark $bookmark): JsonResponse
    {
        $bookmark->update($request->validated());

        return response()->json([
            'data' => new BookmarkResource($bookmark),
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
