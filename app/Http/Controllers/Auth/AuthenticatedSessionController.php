<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\EmailVerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(
        LoginRequest $request,
        EmailVerificationCodeService $verificationCodes,
    ): RedirectResponse {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();
        if ($user && ! $user->hasVerifiedEmail()) {
            $hasActiveCode = $user->email_verification_code
                && $user->email_verification_code_expires_at
                && $user->email_verification_code_expires_at->isFuture();

            if (! $hasActiveCode) {
                try {
                    $verificationCodes->send($user);

                    return redirect()->route('verification.notice')
                        ->with('status', 'verification-code-sent');
                } catch (Throwable $exception) {
                    Log::warning('Gagal mengirim kode verifikasi email saat login.', [
                        'user_id' => $user->id,
                    ]);

                    return redirect()->route('verification.notice')
                        ->with('status', 'verification-email-failed');
                }
            }

            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('filament.dashboard.pages.dashboard'));
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
