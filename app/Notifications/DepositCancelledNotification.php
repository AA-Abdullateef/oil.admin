<?php

namespace App\Notifications;

use App\Models\Deposit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Deposit $deposit) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Deposit could not be processed - ' . config('app.name'))
            ->greeting("Hello {$notifiable->name},")
            ->line('Your deposit was cancelled and could not be completed at this time.')
            ->line('**Reference:** ' . $this->deposit->reference)
            ->line('**Amount:** ' . number_format((float) $this->deposit->quantity, 8) . ' ' . $this->deposit->asset->symbol)
            ->line('If you believe this is an error or need assistance, please contact support with your reference number.')
            ->action('Contact support', url('/'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'deposit_cancelled',
            'title' => 'Deposit cancelled',
            'body' => 'Your deposit of ' . number_format((float) $this->deposit->quantity, 8) . ' ' . $this->deposit->asset->symbol . ' could not be processed.',
            'category' => 'deposit',
            'priority' => 'high',
            'severity' => 'warning',
            'action' => 'contact_support',
            'reference' => $this->deposit->reference,
            'amount' => (string) $this->deposit->quantity,
            'currency' => $this->deposit->asset->symbol,
        ];
    }
}
