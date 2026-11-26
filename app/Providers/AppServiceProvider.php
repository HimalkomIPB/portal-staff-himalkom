<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
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
        Gate::before(function ($user, $ability) {
            return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() ? true : null;
        });

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            $expireMinutes = (int) Config::get('auth.verification.expire', 60);
            $expiresAt = Carbon::now('Asia/Jakarta')
                ->addMinutes($expireMinutes)
                ->locale('id')
                ->translatedFormat('l, j F Y, H.i').' WIB';

            return (new MailMessage)
                ->subject('Verifikasi Alamat Email - Himalkom IPB')
                ->view('emails.verify-email', [
                    'user' => $notifiable,
                    'verificationUrl' => $url,
                    'expiresAt' => $expiresAt,
                ]);
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $broker = Config::get('auth.defaults.passwords', 'users');
            $expireMinutes = (int) Config::get("auth.passwords.{$broker}.expire", 60);
            $expiresAt = Carbon::now('Asia/Jakarta')
                ->addMinutes($expireMinutes)
                ->locale('id')
                ->translatedFormat('l, j F Y, H.i').' WIB';

            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Atur Ulang Kata Sandi - Himalkom IPB')
                ->view('emails.reset-password', [
                    'user' => $notifiable,
                    'resetUrl' => $resetUrl,
                    'expiresAt' => $expiresAt,
                ]);
        });
    }
}
