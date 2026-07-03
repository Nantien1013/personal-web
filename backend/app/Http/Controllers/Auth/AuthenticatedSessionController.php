<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Handles classic (non-Livewire) session-based login submissions for POST /login.
 *
 * The Volt-powered `pages.auth.login` page (registered as GET /login in routes/auth.php)
 * renders the form and submits via Livewire's own wire:submit AJAX channel; it does not
 * expose a traditional POST endpoint. This controller supplies that endpoint so the app
 * still supports a plain HTML form POST to /login (and so it can be exercised directly
 * in HTTP tests via $this->post('/login', ...)).
 *
 * Authentication logic (including RateLimiter lockout) lives entirely in
 * App\Http\Requests\Auth\LoginRequest::authenticate() — the same rate-limit/lockout
 * strategy used by the Volt login form's LoginForm — so there is a single authoritative,
 * protected `Auth::attempt()` path per login mechanism (no duplicated unprotected attempt
 * here).
 */
class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
