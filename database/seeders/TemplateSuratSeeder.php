<?php
namespace Database\Seeders;

use App\Models\TemplateSurat;
use Illuminate\Database\Seeder;

class TemplateSuratSeeder extends Seeder
{
    public function run()
    {
        TemplateSurat::create([
            'nama_template' => 'Template Penerimaan Magang',
            'jenis_surat' => TemplateSurat::JENIS_PENERIMAAN,
            'content_template' => file_get_contents(resource_path('views/templates/surat_penerimaan.blade.php')),
            'is_active' => true,
            'created_by' => 1, // Admin user ID
        ]);

        TemplateSurat::create([
            'nama_template' => 'Template Sertifikat Magang',
            'jenis_surat' => TemplateSurat::JENIS_SERTIFIKAT,
            'content_template' => file_get_contents(resource_path('views/templates/sertifikat.blade.php')),
            'is_active' => true,
            'created_by' => 1, // Admin user ID
        ]);
    }
}
