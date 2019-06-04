<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Model\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\SignupActivate;

class AuthController extends Controller
{
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'type_user' => isset($data['type_user']) ? $data['type_user'] : 2,
            'password' => Hash::make($data['password']),
            'api_token' => Str::random(80),
            'activation_token' => Str::random(80),
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

        $user->notify(new SignupActivate($user));

        return $this->sendSuccess($user, 'Cadastrado com sucesso. Valide sua conta no e-mail.', 200);
    }

    public function signin(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|exists:users|max:255',
            'password' => 'required',
        ]);
        
        $user = User::where('email', $validatedData['email'])->first();

        if ( !$user->active ) {
            return $this->sendError('Email não validado.', [], 403);
        }

        if (Auth::attempt($validatedData)) {
            $user = Auth::user();
            $success['token'] = $user->createToken(env('APP_NAME'))->accessToken;

            return $this->sendSuccess($success, 'Acesso autorizado.', 200);
        } 
        
        return $this->sendError('Usuário não autorizado.', [], 401);
    }

    public function activate($token)
    {
        $user = User::where('activation_token', $token)->first();
        if ( !$user ) {
            return $this->sendError('Token de validação inválido.', [], 404);
        }
        $user->active = true;
        $user->activation_token = null;
        $user->save();
        $success['token'] = $user->createToken(env('APP_NAME'))->accessToken; 
        return $this->sendSuccess($success, 'Validado com sucesso.', 200);
    }
}
