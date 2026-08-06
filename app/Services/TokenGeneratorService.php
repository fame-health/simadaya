<?php

namespace App\Services;

use Illuminate\Support\Str;

class TokenGeneratorService
{
    /**
     * Generate a secure random token for attendance session.
     * Length: 64 characters.
     */
    public function generate(): string
    {
        return Str::random(64);
    }

    /**
     * Generate a hash for the token to be stored in logs if needed.
     */
    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
