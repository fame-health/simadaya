<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordIndonesian extends ResetPassword
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Permintaan Reset Kata Sandi - SIMADAYA')
            ->greeting('Halo!')
            ->line('Anda menerima email ini karena kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda.')
            ->action('Reset Kata Sandi', url(config('app.url').route('password.reset', $this->token, false).'?email='.$notifiable->getEmailForPasswordReset()))
            ->line('Tautan reset kata sandi ini akan kedaluwarsa dalam ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' menit.')
            ->line('Jika Anda tidak merasa melakukan permintaan ini, abaikan saja email ini.')
            ->salutation('Salam hangat,' . "\r\n" . 'Tim SIMADAYA');
    }
}
