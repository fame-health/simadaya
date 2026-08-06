<?php

namespace App\Services\Attendance;

class TokenGeneratorService
{
    public function generate(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
