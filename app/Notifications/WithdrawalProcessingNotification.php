<?php

namespace App\Notifications;

use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalProcessingNotification extends Notification implements ShouldQueue
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
            ->subject('Withdrawal processing - ' . config('app.name'))
            ->greeting("Hello {$notifiable->name},")
            ->line('Your withdrawal request is now being processed.')
            ->line('**Reference:** ' . $this->withdrawal->reference)
            ->line('**Amount:** ' . number_format((float) $this->withdrawal->quantity, 8) . ' ' . $this->withdrawal->asset->symbol)
            ->line('**Method:** ' . ($this->withdrawal->subMethod?->name ?? $this->withdrawal->method?->name))
            ->line('**Destination:** ' . $this->withdrawal->wallet_address_or_bank)
            ->line('**Updated at:** ' . $this->withdrawal->updated_at->format('M d, Y H:i') . ' UTC')
            ->line('Please allow 1-3 business days for the funds to arrive depending on your bank or network.')
            ->action('View transaction history', url('/'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_processing',
            'title' => 'Withdrawal processing',
            'body' => 'Your withdrawal of ' . number_format((float) $this->withdrawal->quantity, 8) . ' ' . $this->withdrawal->asset->symbol . ' is now being processed.',
            'category' => 'withdrawal',
            'priority' => 'high',
            'severity' => 'info',
            'action' => 'view_transaction',
            'reference' => $this->withdrawal->reference,
            'amount' => (string) $this->withdrawal->quantity,
            'currency' => $this->withdrawal->asset->symbol,
        ];
    }
}
