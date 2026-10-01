<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (! Auth::attempt($credentials, false)) {
            return response()->json(['error' => ['message' => 'Email atau kata sandi salah.']], 401);
        }
        $request->session()->regenerate();

        return response()->json(['data' => ['email' => $credentials['email']]]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['data' => ['ok' => true]]);
    }

    public function forgot(ForgotPasswordRequest $request)
    {
        Password::sendResetLink($request->only('email'));

        // Selalu balas sama agar tidak membocorkan email terdaftar atau tidak.
        return response()->json(['data' => ['ok' => true]]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );
        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['error' => ['message' => 'Token tidak valid atau kedaluwarsa.']], 422);
        }

        return response()->json(['data' => ['ok' => true]]);
    }
}
