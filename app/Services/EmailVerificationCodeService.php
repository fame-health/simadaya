<?php

namespace App\Services;

use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailVerificationCodeService
{
    public function send(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            $user->forceFill([
                'email_verification_code' => hash_hmac('sha256', $code, config('app.key')),
                'email_verification_code_expires_at' => now()->addMinutes(10),
            ])->save();

            Mail::to($user->email)->send(new EmailVerificationCodeMail($user, $code));
        } catch (Throwable $exception) {
            Log::error('email_verification_code.delivery_failed', [
                'user_id' => $user->getKey(),
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
