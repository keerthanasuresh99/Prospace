<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function index()
    {
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $rules = array(
            'email' => 'required|email',
            'password' => 'required|min:6'
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Redirect::back()->with('msg_error', $validator->errors()->first());
        }

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            // return redirect()->intended('dashboard');
            return redirect()->route('list-achievers');
        } else {
            return 'login error';
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->intended('dashboard');
    }
}
