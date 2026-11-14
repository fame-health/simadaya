<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyEmailCodeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $result = DB::transaction(function () use ($user, $validated): string {
            $user = $user->newQuery()->lockForUpdate()->findOrFail($user->getKey());

            if ($user->hasVerifiedEmail()) {
                return 'verified';
            }

            if (! $user->email_verification_code || ! $user->email_verification_code_expires_at || $user->email_verification_code_expires_at->isPast()) {
                $user->forceFill([
                    'email_verification_code' => null,
                    'email_verification_code_expires_at' => null,
                ])->save();

                return 'expired';
            }

            $submittedHash = hash_hmac('sha256', $validated['code'], config('app.key'));
            if (! hash_equals($user->email_verification_code, $submittedHash)) {
                return 'invalid';
            }

            $user->markEmailAsVerified();
            $user->forceFill([
                'email_verification_code' => null,
                'email_verification_code_expires_at' => null,
            ])->save();

            return 'verified';
        });

        if ($result === 'expired') {
            return back()->withErrors(['code' => 'Kode verifikasi sudah kedaluwarsa. Kirim kode baru lalu coba lagi.']);
        }

        if ($result === 'invalid') {
            return back()->withErrors(['code' => 'Kode verifikasi tidak sesuai.']);
        }

        return redirect()->intended(route('filament.dashboard.pages.dashboard'))
            ->with('status', 'email-verified');
    }
}
