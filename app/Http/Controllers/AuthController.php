<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Akun yang dinonaktifkan admin tidak bisa masuk.
        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah, atau akun Anda sedang dinonaktifkan.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->homeRoute()));
    }

    public function showRegister(): View
    {
        return view('auth.register', ['regions' => Region::orderBy('name')->get()]);
    }

    /** Pendaftaran publik selalu menjadi "warga". Admin & petugas dibuat oleh admin. */
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'region_id' => ['required', 'exists:regions,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'region_id' => $data['region_id'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);
        $user->assignRole(User::ROLE_CITIZEN);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Selamat datang di SIGAP, ' . $user->name . '! Akun Anda sudah aktif.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
