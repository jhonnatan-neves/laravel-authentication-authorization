<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Gate;

class DashboardController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware('auth:api');

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            
            if (Gate::denies('isAdmin')) {
                return $this->sendError('Acesso não autorizado.', 403);
            }

            return $next($request);
        });
    }

    public function index()
    {    
        return $this->sendSuccess($this->user, 'Acesso autorizado.', 200);
    }
}
