<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Show login form
    public function showLoginForm(Request $request)
    {
        // If already authenticated, redirect to intended page
        if (Auth::check()) {
            return redirect()->intended(route('gallery'));
        }
        
        // Store intended URL if provided
        if ($request->has('redirect')) {
            session(['url.intended' => $request->get('redirect')]);
        }
        
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'captcha' => 'required|numeric',
            'captcha_answer' => 'required|numeric',
        ]);

        // Validate CAPTCHA
        if ($request->captcha != $request->captcha_answer) {
            return back()->withErrors([
                'captcha' => 'Jawaban CAPTCHA salah. Silakan coba lagi.',
            ])->withInput($request->only('email'));
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('gallery'))->with('success', 'Selamat datang, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    // Show register form
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Handle registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'captcha' => 'required|numeric',
            'captcha_answer' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate CAPTCHA
        if ($request->captcha != $request->captcha_answer) {
            return back()->withErrors([
                'captcha' => 'Jawaban CAPTCHA salah. Silakan coba lagi.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('gallery')->with('success', 'Registrasi berhasil! Selamat datang, ' . $user->name . '!');
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('gallery')->with('success', 'Anda telah logout.');
    }
}
