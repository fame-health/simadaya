<?php

namespace App\Livewire\Attendance;

use App\Models\Mahasiswa;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class QRScanner extends Component
{
    public $message = '';
    public $status = ''; // success, error
    public bool $hasAttendedToday = false;

    public function mount()
    {
        $user = Auth::user();
        $student = \App\Models\Mahasiswa::where('user_id', $user->id)->first();

        if ($student) {
            $this->hasAttendedToday = \App\Models\AttendanceLog::where('student_id', $student->id)
                ->whereDate('scan_time', now()->toDateString())
                ->exists();

            if ($this->hasAttendedToday) {
                $this->status = 'success';
                $this->message = 'Anda sudah melakukan absensi hari ini. Silakan kembali besok!';
            }
        }
    }

    public function processResult($qrData)
    {
        try {
            $data = json_decode($qrData, true);

            if (!$data || !isset($data['session_id'], $data['token'], $data['expired_at'])) {
                $this->status = 'error';
                $this->message = 'Format QR Code tidak valid.';
                return;
            }

            $user = Auth::user();
            // Get student profile from user
            $student = Mahasiswa::where('user_id', $user->id)->first();

            if (!$student) {
                $this->status = 'error';
                $this->message = 'Data mahasiswa tidak ditemukan.';
                return;
            }

            $attendanceService = app(AttendanceService::class);
            $result = $attendanceService->processScan(
                $data['session_id'],
                $data['token'],
                $data['expired_at'],
                $student
            );

            if ($result['success']) {
                $this->status = 'success';
                $this->message = $result['message'];
            } else {
                $this->status = 'error';
                $this->message = $result['message'];
            }
        } catch (\Exception $e) {
            $this->status = 'error';
            $this->message = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.attendance.q-r-scanner');
    }
}
