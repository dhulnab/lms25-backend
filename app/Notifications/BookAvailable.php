<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class BookAvailable extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $book, $message, $user;

    public function __construct($book, $message, $user)
    {
        $this->book = $book;
        $this->message = $message;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class, 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Book Now Available',
            'message' => 'The book you requested {$book->title} is now available for pickup.',
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Book Now Available')
            ->body('The book you requested {$book->title} is now available for pickup.');
    }
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        return (new MailMessage)
            ->subject('Book Availability Notification')
            ->line($this->message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'book_id' => $this->book->id,
            'message' => 'The book "' . $this->book->title . '" is now available for pickup, you can come and pick it up at the library.',
        ];
    }
}
