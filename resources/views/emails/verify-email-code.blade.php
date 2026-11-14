@component('mail::message')
# Verifikasi Email SIMADAYA

Halo {{ $user->name }},

Gunakan kode berikut untuk memverifikasi alamat email Anda:

@component('mail::panel')
**{{ $code }}**
@endcomponent

Kode berlaku selama 10 menit. Jika Anda tidak membuat akun SIMADAYA, abaikan email ini.

Salam hangat,<br>
SIMADAYA
@endcomponent
