<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function ($user, string $token) {
            $frontendBaseUrl = rtrim(config('services.frontend_url', config('app.url')), '/');

            return $frontendBaseUrl.'/reset-password?token='.$token.'&email='.urlencode($user->getEmailForPasswordReset());
        });

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $frontendBaseUrl = rtrim(config('services.frontend_url', config('app.url')), '/');
            $resetUrl = $frontendBaseUrl.'/reset-password?token='.$token.'&email='.urlencode($notifiable->getEmailForPasswordReset());

            return (new MailMessage)
                ->subject('Reset Password')
                ->greeting('Halo!')
                ->line('Kami menerima permintaan untuk mereset password akun Anda.')
                ->line('Gunakan kode berikut untuk memverifikasi permintaan Anda: '.$token)
                ->action('Reset Password', $resetUrl)
                ->line('Jika Anda tidak meminta reset password, abaikan email ini.');
        });
    }
}
