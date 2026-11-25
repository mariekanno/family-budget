<?php

namespace App\Providers;

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
        \Illuminate\Auth\Notifications\VerifyEmail::toMailUsing(function ($user, $url) {
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('【Family Budget】メールアドレスの確認')
                ->greeting('こんにちは。')
                ->line('Family Budgetをご利用いただきありがとうございます。')
                ->line('下のボタンを押してメールアドレスを確認してください。')
                ->action('メールアドレスを確認する', $url)
                ->line('このメールに心当たりがない場合は、何もする必要はありません。')
                ->salutation('Family Budget');
        });
        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function ($user, $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]);
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('【Family Budget】パスワードの再設定')
                ->greeting('こんにちは。')
                ->line('パスワードの再設定がリクエストされました。')
                ->action('パスワードを再設定する', $url)
                ->line('このリンクの有効期限は'.config('auth.passwords.users.expire').'分です。')
                ->line('このメールに心当たりがない場合は、何もする必要はありません。')
                ->salutation('Family Budget');
        });
        \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        //
    }
}
