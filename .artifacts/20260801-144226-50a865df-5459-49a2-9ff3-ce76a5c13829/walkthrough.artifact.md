# Update Final: Informasi Akun Terpadu (View & Edit)

Saya telah menyempurnakan fitur pengelolaan akun di menu Biodata Mahasiswa agar muncul secara konsisten baik saat Anda hanya melihat data maupun saat sedang mengubahnya.

## Perubahan yang Dilakukan:

1.  **Mode Lihat (View / Infolist)**:
    - Menambahkan section **"Informasi Akun"** di halaman detail biodata.
    - Mahasiswa kini bisa langsung melihat **Nama Lengkap** dan **Email Akun (Gmail)** segera setelah membuka menu Biodata.
2.  **Mode Ubah (Edit / Form)**:
    - Memastikan section edit (Nama, Gmail, dan Password) tampil di bagian paling atas saat tombol Edit ditekan.
    - Mahasiswa dapat memperbarui password login mereka langsung dari sini.
3.  **Layout Profesional**:
    - Menata ulang halaman detail agar lebih terstruktur: Informasi Akun -> Data Akademik -> Data Pribadi.
    - Menambahkan ikon yang sesuai untuk Email (`envelope`) dan Nama (`user`) agar visual lebih menarik.

## File yang Diperbarui:
- [ViewMahasiswa.php](file:///C:/SKRIPSI/apk/app/Filament/Resources/MahasiswaResource/Pages/ViewMahasiswa.php): Update tampilan detail (View).
- [MahasiswaResource.php](file:///C:/SKRIPSI/apk/app/Filament/Resources/MahasiswaResource.php): Update tampilan form (Edit).

## Cara Verifikasi:
1. Masuk ke menu **Biodata Mahasiswa**.
2. Pastikan kotak **"Informasi Akun"** sudah muncul di bagian atas (menampilkan Nama & Gmail).
3. Klik tombol kuning **"Edit Data"**.
4. Pastikan di halaman edit, Anda bisa mengubah Nama, Email, dan Password di section paling atas.
