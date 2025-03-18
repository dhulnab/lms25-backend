<?php

namespace App\Notifications;

use Ichtrojan\Otp\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationNotification extends Notification
{
    use Queueable;
    public $subject;
    public $message;
    public $formEmail;
    public $mailer;
    private $otp;

    /**
     * Create a new notification instance.
     */
    public function __construct($subject = 'Verification Needed', $message = 'Use the below code for verification process', $formEmail = 'G1RbM@example.com')
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->formEmail = $formEmail;
        $this->mailer = 'smtp';
        $this->otp = new Otp();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $otp = $this->otp->generate($notifiable->email, 'numeric', 6, 60);

        return (new MailMessage)
            ->mailer('smtp')
            ->from($this->formEmail, config('app.name'))
            ->subject($this->subject)
            ->greeting('Hello! ' . $notifiable->name)
            ->line($this->message)
            ->line('Code: ' . $otp->token);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
