<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Withdrawal $withdrawal) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Withdrawal request could not be processed - ' . config('app.name'))
            ->greeting("Hello {$notifiable->name},")
            ->line('Your withdrawal request was reviewed and could not be processed.')
            ->line('**Reference:** ' . $this->withdrawal->reference)
            ->line('**Amount:** ' . number_format((float) $this->withdrawal->quantity, 8) . ' ' . $this->withdrawal->asset->symbol)
            ->when($this->withdrawal->admin_notes, fn ($message) =>
                $message->line('**Reason:** ' . $this->withdrawal->admin_notes)
            )
            ->line('Your balance has not been affected. Please contact support if you have questions.')
            ->action('Contact support', url('/'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_cancelled',
            'title' => 'Withdrawal cancelled',
            'body' => 'Your withdrawal of ' . number_format((float) $this->withdrawal->quantity, 8) . ' ' . $this->withdrawal->asset->symbol . ' was not processed.',
            'category' => 'withdrawal',
            'priority' => 'high',
            'severity' => 'warning',
            'action' => 'contact_support',
            'reference' => $this->withdrawal->reference,
            'amount' => (string) $this->withdrawal->quantity,
            'currency' => $this->withdrawal->asset->symbol,
        ];
    }
}
