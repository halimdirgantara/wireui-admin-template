<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to the Admin Dashboard!',
            'message' => 'Thank you for joining us, ' . $this->user->name . '. Get started by completing your profile.',
            'action_url' => route('profile.edit'),
            'user_id' => $this->user->id,
            'created_by' => 'system',
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to the Admin Dashboard!',
            'message' => 'Thank you for joining us, ' . $this->user->name . '.',
            'user_id' => $this->user->id,
        ];
    }
}
