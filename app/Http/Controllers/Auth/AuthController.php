<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request){
        $fields = $request->validate([
            'username' => 'required|min:4|max:16|regex:/^\S*$/u|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        $user = User::create($fields);

        return response()->json([
            'messages' => 'Registration successfully.',
            'user' => $user,
        ], 201);
    }
}
