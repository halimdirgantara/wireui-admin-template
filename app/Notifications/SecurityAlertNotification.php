<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SecurityAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $alertType;
    protected $details;

    public function __construct($alertType, $details = [])
    {
        $this->alertType = $alertType;
        $this->details = $details;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $messages = [
            'login_from_new_location' => 'New login detected from an unfamiliar location.',
            'password_changed' => 'Your password was recently changed.',
            'email_changed' => 'Your email address was recently changed.',
            'multiple_failed_logins' => 'Multiple failed login attempts detected.',
            'suspicious_activity' => 'Suspicious activity detected on your account.',
        ];

        return [
            'type' => 'security_alert',
            'title' => 'Security Alert',
            'message' => $messages[$this->alertType] ?? 'Security alert for your account.',
            'alert_type' => $this->alertType,
            'details' => $this->details,
            'url' => route('admin.activity-logs.index'),
            'created_by' => 'system',
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'security_alert',
            'title' => 'Security Alert',
            'alert_type' => $this->alertType,
            'details' => $this->details,
        ];
    }
}