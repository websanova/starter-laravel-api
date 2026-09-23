<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PreferenceScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Preference\UpdateRequest;
use Illuminate\Http\JsonResponse;

class PreferenceController extends Controller
{
    /**
     * Merge a partial set of preferences into the user's admin scope.
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->setPreferences(PreferenceScope::Admin, $request->validated());

        return response()->json([
            'data' => $user->preferencesFor(PreferenceScope::Admin),
        ]);
    }
}
