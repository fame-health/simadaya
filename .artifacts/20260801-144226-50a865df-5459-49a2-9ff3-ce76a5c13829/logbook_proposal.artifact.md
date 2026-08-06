# Proposal Fitur: Logbook Mingguan Mahasiswa (SIMADAYA)

Fitur ini dirancang untuk mendokumentasikan kegiatan, progres, dan kendala mahasiswa magang setiap minggunya secara terstruktur.

## 1. Alur Sistem (Workflow)

1.  **Mahasiswa**:
    *   Setiap akhir minggu (misal Jumat/Sabtu), mahasiswa mengisi Logbook.
    *   Data yang diisi: Ringkasan kegiatan, hasil yang dicapai, kendala, dan lampiran (foto/file PDF kegiatan).
    *   Mahasiswa bisa melihat riwayat logbook mereka sendiri.
2.  **Pembimbing (Mentor)**:
    *   Menerima notifikasi atau melihat daftar logbook mahasiswa bimbingannya.
    *   Memberikan **Feedback/Komentar** dan **Penilaian Kualitatif** (Validasi).
    *   Menandai logbook sebagai "Disetujui" atau "Perlu Perbaikan".
3.  **Admin**:
    *   Memantau kepatuhan pengisian logbook secara global (siapa yang rajin isi vs yang bolos).
    *   Melihat rekapitulasi kegiatan seluruh mahasiswa untuk laporan internal.

---

## 2. Struktur Data (Database Schema)

Tabel baru: `weekly_logbooks`
*   `id`: Primary Key
*   `mahasiswa_id`: Foreign Key (Relasi ke Mahasiswa)
*   `week_number`: Minggu ke-berapa (1, 2, 3...)
*   `start_date` & `end_date`: Rentang tanggal minggu tersebut.
*   `activities`: Text (Apa saja yang dikerjakan).
*   `achievements`: Text (Apa hasilnya).
*   `problems`: Text (Kendala yang dihadapi).
*   `attachment`: String (Path file lampiran kegiatan).
*   `mentor_feedback`: Text (Komentar dari pembimbing).
*   `status`: Enum (Draft, Submitted, Approved, Rejected).

---

## 3. Tampilan di Filament Dashboard

### A. Untuk Mahasiswa
*   Form input yang bersih dengan *rich text editor* untuk deskripsi kegiatan.
*   Upload file untuk bukti kegiatan.
*   Status progres (Apakah sudah dikomentari mentor atau belum).

### B. Untuk Pembimbing
*   Tabel daftar logbook yang perlu diperiksa (Filter: *Waiting Approval*).
*   Tombol aksi cepat "Approve" atau kolom komentar untuk feedback langsung.
*   Statistik mingguan: "Minggu ini 8/10 mahasiswa sudah isi logbook".

### C. Untuk Admin
*   Laporan rekapitulasi logbook yang bisa di-export ke PDF/Excel.

---

## 4. Keunggulan Fitur Ini
*   **Terintegrasi**: Terhubung langsung dengan data mahasiswa dan pembimbing yang sudah ada.
*   **Responsif**: Mudah diisi melalui HP saat mahasiswa masih di lokasi magang.
*   **Monitoring**: Menjamin adanya komunikasi dua arah antara mahasiswa dan pembimbing setiap minggu.

> [!NOTE]
> Apakah alur ini sudah sesuai dengan kebutuhan Anda? Atau ada data khusus (misal: jumlah jam kerja mingguan) yang ingin ditambahkan?
