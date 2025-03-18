<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = Firebase::messaging();
    }

    public function sendNotification(string $token, string $title, string $message, ?string $link = null): bool
    {
        $notification = Notification::create($title, $message);

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification);

        if ($link) {
            $message = $message->withWebPushConfig([
                'fcm_options' => ['link' => $link],
            ]);
        }

        try {
            $this->messaging->send($message);
            return true;
        } catch (\Exception $e) {
            Log::error('Firebase notification failed: ' . $e->getMessage());
            return false;
        }
    }
}
