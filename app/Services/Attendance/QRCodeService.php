<?php

namespace App\Services\Attendance;

use App\Models\AttendanceSession;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Carbon;
use JsonException;

class QRCodeService
{
    /**
     * @return array{session_id:int,token:string,expired_at:string}
     */
    public function payloadForSession(AttendanceSession $session): array
    {
        if (! $session->current_token || ! $session->expires_at) {
            throw new AttendanceValidationException('inactive_session', 'Token absensi belum tersedia.');
        }

        return [
            'session_id' => $session->id,
            'token' => $session->current_token,
            'expired_at' => $session->expires_at
                ->copy()
                ->timezone(config('attendance.timezone'))
                ->format('Y-m-d H:i:s'),
        ];
    }

    public function payloadJson(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public function svgForPayload(array $payload): string
    {
        $qrCode = QrCode::create($this->payloadJson($payload))
            ->setSize(360)
            ->setMargin(12)
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setForegroundColor(new Color(17, 24, 39))
            ->setBackgroundColor(new Color(255, 255, 255));

        return (new SvgWriter())
            ->write($qrCode, null, null, [
                SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
            ])
            ->getString();
    }

    public function dataUriForPayload(array $payload): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->svgForPayload($payload));
    }

    public function dataUriForSession(AttendanceSession $session): string
    {
        return $this->dataUriForPayload($this->payloadForSession($session));
    }

    /**
     * @return array{session_id:int,token:string,expired_at:string}
     */
    public function parsePayload(string|array $payload): array
    {
        if (is_string($payload)) {
            try {
                $payload = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                throw new AttendanceValidationException('invalid_qr', 'Format QR Code tidak valid.');
            }
        }

        if (! is_array($payload)) {
            throw new AttendanceValidationException('invalid_qr', 'Format QR Code tidak valid.');
        }

        $sessionId = $payload['session_id'] ?? null;
        $token = $payload['token'] ?? null;
        $expiredAt = $payload['expired_at'] ?? null;

        if (! is_numeric($sessionId) || ! is_string($token) || strlen($token) !== 64 || ! is_string($expiredAt)) {
            throw new AttendanceValidationException('invalid_qr', 'Isi QR Code tidak lengkap.');
        }

        try {
            Carbon::parse($expiredAt, config('attendance.timezone'));
        } catch (\Throwable) {
            throw new AttendanceValidationException('invalid_qr', 'Waktu kedaluwarsa QR tidak valid.');
        }

        return [
            'session_id' => (int) $sessionId,
            'token' => $token,
            'expired_at' => $expiredAt,
        ];
    }
}
