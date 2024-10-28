<?php

namespace App\Notifications;

use App\Models\LoyaltyAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoyaltyPointsDepositNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        readonly public float $points_amount,
        readonly public float $balance
    )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(LoyaltyAccount $notifiable): array
    {
        $types = [];

        if ($notifiable->email != '' && $notifiable->email_notification) {
            $types[] = 'mail';
        }

        if ($notifiable->phone != '' && $notifiable->phone_notification) {
            $types[] = 'sms';
        }

        return $types;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(LoyaltyAccount $notifiable)
    {
        return (new MailMessage)
            ->view('emails.loyaltyPointsReceived', [
                'balance' => $this->balance,
                'points' => $this->points_amount,
            ]);
    }

    public function toSms(LoyaltyAccount $notifiable)
    {
        // Тут оправка смс
    }

}
