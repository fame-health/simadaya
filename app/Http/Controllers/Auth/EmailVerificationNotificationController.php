<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request, EmailVerificationCodeService $verificationCodes): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('filament.dashboard.pages.dashboard'));
        }

        $throttleKey = 'email-verification-code-resend:'.$user->getKey();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return back()
                ->with('status', 'verification-code-throttled')
                ->with('retry_after', RateLimiter::availableIn($throttleKey));
        }

        RateLimiter::hit($throttleKey, 60);

        try {
            $verificationCodes->send($user);
        } catch (Throwable $exception) {
            Log::warning('Gagal mengirim ulang kode verifikasi email.', [
                'user_id' => $user->id,
            ]);

            return back()->with('status', 'verification-email-failed');
        }

        return back()->with('status', 'verification-code-sent');
    }
}
