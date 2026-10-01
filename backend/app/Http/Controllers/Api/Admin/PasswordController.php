<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function update(UpdatePasswordRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json(['error' => ['message' => 'Kata sandi saat ini salah.', 'code' => 'VALIDATION_ERROR', 'fields' => ['current_password' => ['Kata sandi saat ini salah.']]]], 422);
        }
        $user->forceFill(['password' => Hash::make($data['password'])])->save();
        Auth::guard('web')->logoutOtherDevices($data['current_password']);

        return response()->json(['data' => ['ok' => true]]);
    }
}
