<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'mail' => ['required', 'email', 'max:255', 'unique:usuarios,mail'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $usuario = Usuario::create([
            'nombre' => $data['nombre'],
            'mail' => $data['mail'],
            'password' => $data['password'],
        ]);

        $token = $usuario->createToken('auth')->plainTextToken;

        return response()->json([
            'usuario' => $usuario,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'mail' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('mail', $data['mail'])->first();

        if (! $usuario || ! Hash::check($data['password'], $usuario->password)) {
            throw ValidationException::withMessages([
                'mail' => ['Las credenciales no coinciden con nuestros registros.'],
            ]);
        }

        $token = $usuario->createToken('auth')->plainTextToken;

        return response()->json([
            'usuario' => $usuario,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesion cerrada.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
