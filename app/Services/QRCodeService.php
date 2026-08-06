<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeService
{
    /**
     * Generate QR Code as SVG string.
     */
    public function generate(int $sessionId, string $token, string $expiredAt): string
    {
        $payload = json_encode([
            'session_id' => $sessionId,
            'token' => $token,
            'expired_at' => $expiredAt,
        ]);

        return QrCode::size(300)
            ->format('svg')
            ->margin(1)
            ->errorCorrection('H')
            ->generate($payload);
    }
}
