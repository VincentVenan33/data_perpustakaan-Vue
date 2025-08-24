<?php

// app/Http/Controllers/Api/UserController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    public function index()
    {
        return User::orderBy('created_at', 'desc')->get();
    }


    public function store(Request $request)
    {
        // Atur validasi
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:5',
        ], [
            'name.required'     => 'Nama tidak boleh kosong',
            'email.required'    => 'Email tidak boleh kosong',
            'email.email'       => 'Email harus mengandung @',
            'password.required' => 'Password tidak boleh kosong',
            'password.min'      => 'Password harus minimal 5 huruf',
        ]);

        // Kalau gagal, kirim pesan error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Hash password dan simpan user
        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data'    => $user
        ]);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail user ditemukan',
            'data' => $user
        ]);
    }
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validasi manual agar bisa pakai pesan custom
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:5',
        ], [
            'name.required' => 'Nama tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Email harus mengandung @',
            'email.unique' => 'Email sudah digunakan',
            'password.min' => 'Password harus minimal 5 huruf',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $validator->validated();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User telah dihapus']);
    }
}
