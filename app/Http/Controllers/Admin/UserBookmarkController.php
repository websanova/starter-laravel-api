<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserBookmark\DestroyRequest;
use App\Http\Requests\Admin\UserBookmark\IndexRequest;
use App\Http\Resources\Admin\BookmarkResource;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserBookmarkController extends Controller
{
    /**
     * List a user's bookmarks with optional filtering and sorting.
     */
    public function index(IndexRequest $request, User $user): JsonResponse
    {
        $bookmarks = $user->bookmarks()
            ->forFavorited($request->validated('favorited'))
            ->forCategory($request->validated('category_id'))
            ->sortBy($request->validated('sort_by'), $request->validated('sort_dir'))
            ->paginate($request->validated('per_page', 15));

        return response()->json(BookmarkResource::paginated($bookmarks));
    }

    /**
     * Delete a user's bookmark.
     */
    public function destroy(DestroyRequest $request, User $user, Bookmark $bookmark): JsonResponse
    {
        $bookmark->delete();

        return response()->json(null, 204);
    }
}
