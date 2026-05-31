<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Important: Your account has been suspended - ' . config('app.name'))
            ->greeting("Hello {$notifiable->name},")
            ->line('Your account has been suspended. You will not be able to log in or perform transactions until this is resolved.')
            ->line('If you believe this is a mistake or would like to appeal, please contact support immediately.')
            ->action('Contact support', url('/'))
            ->line(config('app.name') . ' Trust & Safety team.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'account_suspended',
            'title' => 'Account suspended',
            'body' => 'Your account has been suspended. Please contact support for assistance.',
            'category' => 'security',
            'priority' => 'critical',
            'severity' => 'danger',
            'action' => 'contact_support',
        ];
    }
}
