<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\User;

class AuthController extends Controller
{
    // Langsung handle login action
    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Mohon masukkan email dan password terlebih dahulu',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        // Cek apakah user dengan email tersebut ada
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email belum ditemukan',
            ], 404);
        }

        // Coba login
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil login!',
                'user' => Auth::user(),
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Password belum benar',
        ], 401);
    }

    // Langsung handle logout action
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout!',
        ]);
    }
}
