<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Factory;
use NotificationChannels\Fcm\FcmChannel;
use Kreait\Firebase\Messaging\CloudMessage;
use Minishlink\WebPush\WebPush;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NotifyByAll extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $title, $message, $book;
    public function __construct($title, $message, $book)
    {
        $this->title = $title;
        $this->message = $message;
        $this->book = $book;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', FcmChannel::class, 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($this->message)
            ->line('Thank you for using our application!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
        ];
    }
    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->setData(['click_action' => 'FLUTTER_NOTIFICATION_CLICK'])
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->setTitle($this->title)
                    ->setBody($this->message)
            );
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'book' => $this->book,
            'message' => $this->message,
        ];
    }
}
