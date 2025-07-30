<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: ['show'])
        ];
    }

    public function show(Profile $profile)
    {
        return response()->json($profile);
    }

    public function update(UpdateProfileRequest $request, Profile $profile)
    {
        Gate::authorize('updateprofile', $profile);
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            if ($profile->avatar && Storage::disk('public')->exists($profile->avatar)) {
                Storage::disk('public')->delete($profile->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $profile
        ]);
    }
}
