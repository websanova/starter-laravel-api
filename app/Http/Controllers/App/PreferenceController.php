<?php

namespace App\Http\Controllers\App;

use App\Enums\PreferenceScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Preference\UpdateRequest;
use Illuminate\Http\JsonResponse;

class PreferenceController extends Controller
{
    /**
     * Merge a partial set of preferences into the user's app scope.
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->setPreferences(PreferenceScope::App, $request->validated());

        return response()->json([
            'data' => $user->preferencesFor(PreferenceScope::App),
        ]);
    }
}
