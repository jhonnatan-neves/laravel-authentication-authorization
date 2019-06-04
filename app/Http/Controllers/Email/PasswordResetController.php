<?php

namespace App\Http\Controllers\Email;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\Model\User;
use App\Model\PasswordReset;

class PasswordResetController extends Controller
{
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|exists:users|max:255|email',   
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if ( !$user ) {
            $message = 'Não foi encontrado nenhum usuário com essse email.';
            
            return $this->sendError($message, [], 404);
        }

        $passwordReset = PasswordReset::updateOrCreate(
            [
                'email' => $user->email
            ],
            [
                'email' => $user->email,
                'token' => Str::random(80)
            ]
        );

        if ( !$user || !$passwordReset ) {
            $message = 'Erro na solicitação, tente novamente.';
            
            return $this->sendError($message, [], 500);
        }

        $user->notify(
            new PasswordResetRequest($passwordReset->token)
        );

        $message = 'Nós enviamos um email com o link para resetar sua senha!';
        return $this->sendSuccess($user, $message, 200);
    }

    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)
            ->first();

        if ( !$passwordReset ) {
            $message = 'Este token é inválido.';
            
            return $this->sendError($message, [], 404);
        }

        if (Carbon::parse($passwordReset->created_at)->addMinutes(1)->isPast()) {
            $passwordReset->delete();

            $message = 'Este token foi expirado.';
            
            return $this->sendError($message, [], 404);
        }

        return $this->sendSuccess($passwordReset, 'Validado com sucesso.', 200);
    }

    public function reset(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'token' => 'required|string'
        ]);

        $passwordReset = PasswordReset::where([
            ['token', $validatedData['token']],
            ['email', $validatedData['email']]
        ])->first();

        if ( !$passwordReset ) {
            $message = 'Este token é inválido.';
            
            return $this->sendError($message, [], 404);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if ( !$user ) {
            $message = 'Email não encontrado.';
            
            return $this->sendError($message, [], 404);
        }

        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess($passwordReset));
        
        $message = 'Senha alterada com sucesso.';
        return $this->sendSuccess($user, $message, 200);
    }
}
