<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountPruneStrategy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserForceDelete\DestroyRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserForceDeleteController extends Controller
{
    /**
     * Force delete a user using the configured prune strategy.
     */
    public function destroy(DestroyRequest $request, User $user): JsonResponse
    {
        $strategy = config('auth.delete.prune_strategy');

        match ($strategy) {
            AccountPruneStrategy::Delete => $user->purge(),
            AccountPruneStrategy::Anonymize => $user->anonymize(),
        };

        return response()->json(null, 204);
    }
}
