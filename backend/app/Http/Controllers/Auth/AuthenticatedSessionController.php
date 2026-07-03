<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Handles classic (non-Livewire) session-based login submissions for POST /login.
 *
 * The Volt-powered `pages.auth.login` page (registered as GET /login in routes/auth.php)
 * renders the form and submits via Livewire's own wire:submit AJAX channel; it does not
 * expose a traditional POST endpoint. This controller supplies that endpoint so the app
 * still supports a plain HTML form POST to /login (and so it can be exercised directly
 * in HTTP tests via $this->post('/login', ...)).
 */
class AuthenticatedSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => __('auth.failed'),
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
