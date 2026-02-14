<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validação
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Busca usuário
        $user = User::where('email', $request->email)->first();

        // Verifica credenciais
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        // Cria token
        $token = $user->createToken($user->role . '-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role'  => $user->role
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->tokens()?->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    public function register(Request $request)
    {
        // Validação

    // Validação
    $validator = Validator::make($request->all(), [
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users,email',
        'password'  => 'required|min:6',
        'cpassword' => 'required|same:password',
        'role'      => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Erro de validação',
            'errors'  => $validator->errors()
        ], 422);
    }

    // Garante que não haverá created_at/updated_at vindos do request
    $data = $request->only(['name', 'email', 'role']);
    $data['password'] = Hash::make($request->password);

    // Criação do usuário usando Eloquent (Eloquent preencherá created_at/updated_at automaticamente)
    $user = User::create($data);

    // Caso queira garantir timestamps corretos manualmente:
    // $user->created_at = now();
    // $user->updated_at = now();
    // $user->save();

    // Token
    $token = $user->createToken('MyApp')->plainTextToken;

    return response()->json([
        'token' => $token,
        'name'  => $user->name
    ], 201);

    }
}
