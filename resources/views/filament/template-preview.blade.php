@php
    $content = null;
    $record = null;

    if (isset($get) && is_callable($get)) {
        $content = $get('content_template');
        $record = $get('record'); // Get the TemplateSurat record if available
    } elseif (isset($content_template)) {
        $content = $content_template;
    }

    $data = [
        'nomor_surat' => $record && $record->nomer_surat ? $record->nomer_surat : '123/SRT/2025',
        'nama' => 'John Doe',
        'nim' => '123456789',
        'periode_magang' => '1 Januari 2025 - 30 Juni 2025',
        'tanggal' => now()->format('d M Y'),
        'nama_kepala_dinas' => 'Dr. Ahmad Yani',
        'nama_pembimbing' => 'Prof. Budi Santoso',
    ];

    if ($content) {
        $processedContent = str_replace(
            array_map(fn($key) => "{{ $key }}", array_keys($data)),
            array_values($data),
            $content
        );
    } else {
        $processedContent = null;
    }
@endphp

<div class="p-4 border rounded-md bg-gray-50">
    <h3 class="font-semibold mb-2 text-gray-700">Preview Template</h3>

    @if (!empty($processedContent))
        @if (preg_match('/<!DOCTYPE html>.*<\/html>/is', $processedContent))
            <iframe
                id="preview-iframe"
                srcdoc="{!! htmlspecialchars($processedContent, ENT_QUOTES, 'UTF-8') !!}"
                class="w-full h-[800px] border-2 border-gray-300 rounded-md bg-white shadow"
            ></iframe>
            <div class="mt-2 text-right">
                <button
                    type="button"
                    onclick="let w = window.open(); w.document.write(`{!! str_replace('`', '\`', $processedContent) !!}`);"
                    class="px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none"
                >
                    🔎 Buka Fullscreen
                </button>
            </div>
        @else
            <p class="text-red-500 italic">HTML tidak valid. Pastikan menggunakan struktur HTML lengkap (contoh: &lt;html&gt;&lt;body&gt;...&lt;/body&gt;&lt;/html&gt;).</p>
        @endif
    @else
        <p class="text-gray-500 italic">Belum ada isi template untuk ditampilkan.</p>
    @endif
</div>
