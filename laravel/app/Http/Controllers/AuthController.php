<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->session()->put('login_timestamp', now()->format('d M Y, H:i:s').' WIB');
            Cookie::queue(
                'sipus_last_user_email',
                $credentials['email'],
                10080,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'lax',
            );

            return redirect()->intended(route('equipments.index'))
                ->with('success', 'Sesi lab asisten berhasil dimulai.');
        }

        return back()->withInput($request->only('email'))
            ->with('error', 'Email atau kata sandi tidak valid.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesi lab asisten telah diakhiri.');
    }
}
