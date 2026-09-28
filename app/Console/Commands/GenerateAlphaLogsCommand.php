<?php

namespace App\Console\Commands;

use App\Services\Attendance\AttendanceService;
use Illuminate\Console\Command;

class GenerateAlphaLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-alpha-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis mencatat status Alpa untuk peserta magang yang tidak mengisi absensi selama jam kerja/sesi.';

    /**
     * Execute the console command.
     */
    public function handle(AttendanceService $service): int
    {
        $this->info('Memproses pencatatan otomatis status Alpa...');

        $createdCount = $service->autoGenerateAlphaLogs();

        $this->info("Pencatatan selesai. {$createdCount} log Alpa baru berhasil ditambahkan.");

        return Command::SUCCESS;
    }
}
