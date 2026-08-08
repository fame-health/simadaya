<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceSession;
use App\Jobs\RotateAttendanceTokenJob;

class RotateQRCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:rotate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate QR tokens every 10 seconds for 1 minute';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting QR Rotation loop (60 seconds)...");

        // Jalankan loop selama 1 menit (6 kali setiap 10 detik)
        for ($i = 0; $i < 6; $i++) {
            $activeSessions = AttendanceSession::where('status', 'active')->get();

            if ($activeSessions->isEmpty()) {
                $this->line("[" . now()->format('H:i:s') . "] No active sessions found.");
            }

            foreach ($activeSessions as $session) {
                $this->info("[" . now()->format('H:i:s') . "] Rotating session ID: " . $session->id);

                // Gunakan dispatchSync agar langsung dieksekusi
                RotateAttendanceTokenJob::dispatchSync($session->id);
            }

            // Tunggu 10 detik sebelum putaran berikutnya
            if ($i < 5) {
                sleep(10);
            }
        }

        $this->info("Rotation loop finished.");
    }
}
