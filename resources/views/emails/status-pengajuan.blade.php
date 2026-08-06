<h2>Status Pengajuan Magang Anda</h2>

<p>Halo {{ $pengajuan->mahasiswa->user->name }},</p>

<p>Status pengajuan magang Anda sekarang adalah:
    <strong>{{ strtoupper($pengajuan->status) }}</strong>
</p>

@if($pengajuan->status === 'ditolak')
    <p>Alasan Penolakan: {{ $pengajuan->alasan_penolakan }}</p>
@endif

<p>Terima kasih.</p>
