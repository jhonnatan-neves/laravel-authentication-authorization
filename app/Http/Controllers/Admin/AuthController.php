<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'type_user' => isset($data['type_user']) ? $data['type_user'] : 2,
            'password' => Hash::make($data['password']),
            'api_token' => Str::random(60),
        ]);
    }

    public function signup(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|unique:users|max:255',
            'name' => 'required',
            'password' => 'required',
        ]);

        $user = $this->create($validatedData);

        $success['token'] = $user->createToken(env('APP_NAME'))->accessToken; 

        return $this->sendSuccess($success, 'Cadastrado com sucesso.', 200);
    }

    public function signin(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|exists:users|max:255',
            'password' => 'required',
        ]);

        if (Auth::attempt($validatedData)) {
            $user = Auth::user();
            $success['token'] = $user->createToken(env('APP_NAME'))->accessToken;

            return $this->sendSuccess($success, 'Acesso autorizado.', 200);
        } 
        
        return $this->sendError('Usuário não autorizado.', 403);
    }
}
