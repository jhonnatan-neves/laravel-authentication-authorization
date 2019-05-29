<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class LoginController extends Controller
{
    public function __construct()
    {
        // $this->middleware('guest');
    }

    public function index()
    {
      $data = [
        'username' => 'Jhonnatan',
        'password' => '123'
      ];
      return $this->sendResponse($data, 200);
    }
}
