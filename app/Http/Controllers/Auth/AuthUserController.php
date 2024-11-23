<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthUserController extends Controller
{
    public function registerIndex() {
        return view('page.auth.register');
    }

    public function passwordRecovery() {
        return view('page.auth.passwordRecovery');
    }
}
