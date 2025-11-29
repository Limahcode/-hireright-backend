<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send a notification to the user.
     *
     * @param  User   $user
     * @param  string $title
     * @param  string $message
     * @param  string $type
     * @param  string $channel
     * @return Notification
     */
    public function sendNotification(User $user, $title, $message, $type = 'general', $channel = 'push')
    {
        // Create the notification record
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'status' => 'new'
        ]);

        // Dispatch to the respective channel
        switch ($channel) {
            case 'email':
                $this->sendEmailNotification($user, $notification);
                break;
            case 'sms':
                $this->sendSmsNotification($user, $notification);
                break;
            case 'push':
            default:
                $this->sendPushNotification($user, $notification);
                break;
        }

        return $notification;
    }

    public function archiveNotificationStatus(Notification $notification)
    {
        $notification->updateStatus('archived');
    }

    public function updateNotificationStatus(Notification $notification, $newStatus)
    {
        $notification->updateStatus($newStatus);
    }

    protected function sendEmailNotification(User $user, Notification $notification)
    {
        // Assume Mail setup here; inject a notification email template if available
        //Mail::to($user->email)->send(new \App\Mail\NotificationMail($notification));

    }

    protected function sendSmsNotification(User $user, Notification $notification)
    {
        // Assume SMS service is configured and available
        // SMS::send($user->phone, $notification->message);

    }

    protected function sendPushNotification(User $user, Notification $notification)
    {
        // Assume SMS service is configured and available
        // SMS::send($user->phone, $notification->message);

    }
}
