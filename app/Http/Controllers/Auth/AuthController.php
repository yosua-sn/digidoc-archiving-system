<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Password as Pass;
use Illuminate\Validation\ValidationException;

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
            'role' => 'required'
        ]);

        $user = User::create($fields);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'messages' => 'Registration successfully.',
            'user' => $user,
        ], 201);
    }

    public function emailVerify($user_id, Request $request){
        if (!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Invalid or expired verification email.',
            ], 400);
        }

        $user = User::findOrFail($user_id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 400);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            return response()->json([
                'message' => 'Email address successfully verified',
                'user' => $user,
            ]);
        }

        return response()->json([
            'message' => 'Email address already verified.',
        ], 400);
    }

    public function resendEmailVerificationMail(Request $request){
        $user_id = $request->input('user_id');

        $user = User::findOrFail($user_id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email verification link sent to your email address',
        ]);
    }

    public function login(Request $request){
        $fields = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        $credentials = [
            'email' => $fields['email'],
            'password' => $fields['password'],
        ];

        if (!Auth::attempt($credentials, $fields['remember'])) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        session()->regenerate();

        return response()->json([
            'message' => 'Login successfully.',
            'user' => Auth::user(),
        ], 200);
    }

    public function logout(){
        Auth::guard('web')->logout();

        return response(status: 204);
    }

    public function forgotPassword(Request $request){
        $request->validate(['email' => 'required|email']);

        $status = Pass::sendResetLink(
            $request->only('email')
        );

        return $status === Pass::RESET_LINK_SENT
            ? response()->json([
                'message' => trans($status),
            ])
            : response()->json([
                'message' => trans($status),
            ], 400);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Pass::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Pass::PASSWORD_RESET
            ? response()->json([
                'message' => trans($status),
            ])
            : response()->json([
                'message' => trans($status),
            ], 400);
    }
}
