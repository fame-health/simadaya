@component('mail::message')
# Pendaftaran Berhasil

Halo {{ $user->name }},

Selamat! Akun kamu berhasil didaftarkan di **SIMADAYA**.

Terima kasih telah melakukan pendaftaran.

@component('mail::button', ['url' => url('/')])
Kunjungi Website
@endcomponent

Salam Hangat,
{{ config('app.name') }}
@endcomponent
