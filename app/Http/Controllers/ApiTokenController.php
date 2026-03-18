<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiTokenRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ApiTokenController extends Controller
{
    /**
     * Store a newly generated Sanctum token for the authenticated user.
     */
    public function store(StoreApiTokenRequest $request): RedirectResponse
    {
        $plainTextToken = $request->user()
            ->createToken($request->validated('name'))
            ->plainTextToken;

        return Redirect::route('profile.edit')->with('plainTextToken', $plainTextToken);
    }

    /**
     * Delete a personal access token belonging to the authenticated user.
     */
    public function destroy(Request $request, string $tokenId): RedirectResponse
    {
        $token = $request->user()->tokens()->findOrFail($tokenId);

        $token->delete();

        return Redirect::route('profile.edit');
    }
}
