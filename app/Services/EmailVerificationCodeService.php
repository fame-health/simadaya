<?php

namespace App\Services;

use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailVerificationCodeService
{
    public function send(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'email_verification_code' => hash_hmac('sha256', $code, config('app.key')),
            'email_verification_code_expires_at' => now()->addMinutes(10),
        ])->save();

        Mail::to($user->email)->send(new EmailVerificationCodeMail($user, $code));
    }
}
