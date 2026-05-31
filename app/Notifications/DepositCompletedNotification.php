<?php

namespace App\Notifications;

use App\Models\Deposit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositCompletedNotification extends Notification implements ShouldQueue
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
            ->subject('Your deposit has been completed - ' . config('app.name'))
            ->greeting("Hello {$notifiable->name},")
            ->line('Your deposit has been completed and is now usable in your balance.')
            ->line('**Reference:** ' . $this->deposit->reference)
            ->line('**Amount:** ' . number_format((float) $this->deposit->quantity, 8) . ' ' . $this->deposit->asset->symbol)
            ->line('**Method:** ' . ($this->deposit->subMethod?->name ?? $this->deposit->method?->name))
            ->line('**Updated at:** ' . $this->deposit->updated_at->format('M d, Y H:i') . ' UTC')
            ->action('View balance', url('/'))
            ->line('Thank you for investing with ' . config('app.name') . '.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'deposit_completed',
            'title' => 'Deposit completed',
            'body' => 'Your deposit of ' . number_format((float) $this->deposit->quantity, 8) . ' ' . $this->deposit->asset->symbol . ' has been completed.',
            'category' => 'deposit',
            'priority' => 'normal',
            'severity' => 'success',
            'action' => 'view_balance',
            'reference' => $this->deposit->reference,
            'amount' => (string) $this->deposit->quantity,
            'currency' => $this->deposit->asset->symbol,
        ];
    }
}
