<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Mahasiswa;
use App\Models\PengajuanMagang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatAbsensiPdfController extends Controller
{
    public function download(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        // Auto-generate alpha logs for ended sessions
        app(\App\Services\Attendance\AttendanceService::class)->autoGenerateAlphaLogs();

        $studentId = $request->input('student_id');

        if ($user->isMahasiswa()) {
            $student = $user->mahasiswa;
            if (!$student) {
                abort(404, 'Data mahasiswa tidak ditemukan.');
            }
        } else {
            if ($studentId) {
                $student = Mahasiswa::find($studentId);

                if ($student && $user->isPembimbing() && $user->pembimbing) {
                    $pembimbingId = $user->pembimbing->id;
                    $isAssigned = PengajuanMagang::where('mahasiswa_id', $student->id)
                        ->where('pembimbing_id', $pembimbingId)
                        ->whereIn('status', [PengajuanMagang::STATUS_DITERIMA, PengajuanMagang::STATUS_SELESAI])
                        ->exists();

                    if (!$isAssigned) {
                        abort(403, 'Anda tidak berwenang mengakses data peserta magang ini.');
                    }
                }
            } else {
                if ($user->isPembimbing() && $user->pembimbing) {
                    $pembimbingId = $user->pembimbing->id;
                    $student = Mahasiswa::whereHas('pengajuan', function ($q) use ($pembimbingId) {
                        $q->where('pembimbing_id', $pembimbingId)
                          ->whereIn('status', [PengajuanMagang::STATUS_DITERIMA, PengajuanMagang::STATUS_SELESAI]);
                    })->first();
                } else {
                    $student = Mahasiswa::first();
                }
            }
        }

        if (!$student) {
            return back()->with('error', 'Peserta magang tidak ditemukan.');
        }

        $pengajuan = PengajuanMagang::where('mahasiswa_id', $student->id)
            ->whereIn('status', [PengajuanMagang::STATUS_DITERIMA, PengajuanMagang::STATUS_SELESAI])
            ->latest()
            ->first();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = AttendanceLog::where('student_id', $student->id)->with('session');

        if ($startDate) {
            $query->whereDate('scan_time', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('scan_time', '<=', $endDate);
        }

        $logs = $query->orderBy('scan_time', 'asc')->get();

        $totalHadir = $logs->where('status', AttendanceLog::STATUS_PRESENT)->count();
        $totalIzin = $logs->where('status', AttendanceLog::STATUS_PERMIT)->count();
        $totalSakit = $logs->where('status', AttendanceLog::STATUS_SICK)->count();
        $totalAlpa = $logs->whereIn('status', [AttendanceLog::STATUS_ALPHA, 'alpa'])->count();
        $totalTotal = $logs->count();
        $persentase = $totalTotal > 0 ? round(($totalHadir / $totalTotal) * 100, 1) : 0;

        $stats = [
            'total_hadir' => $totalHadir,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_alpa' => $totalAlpa,
            'total_total' => $totalTotal,
            'persentase_kehadiran' => $persentase,
        ];

        $pdf = Pdf::loadView('pdf.riwayat-absensi', [
            'student' => $student,
            'pengajuan' => $pengajuan,
            'logs' => $logs,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ]);

        $filename = 'Riwayat-Absensi-' . str_replace(' ', '-', $student->user->name ?? 'Peserta') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }
}
