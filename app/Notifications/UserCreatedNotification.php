<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $newUser;
    protected $createdBy;

    public function __construct($newUser, $createdBy)
    {
        $this->newUser = $newUser;
        $this->createdBy = $createdBy;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'user_created',
            'title' => 'New User Created',
            'message' => 'A new user "' . $this->newUser->name . '" has been created by ' . $this->createdBy->name . '.',
            'user_id' => $this->newUser->id,
            'created_by_id' => $this->createdBy->id,
            'url' => route('admin.users.show', $this->newUser->id),
            'created_by' => $this->createdBy->name,
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'user_created',
            'title' => 'New User Created',
            'user_id' => $this->newUser->id,
            'created_by_id' => $this->createdBy->id,
        ];
    }
}