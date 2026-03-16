<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class JobRequestNotification extends Notification
{
    use Queueable;

    protected $jobRequest;

    public function __construct($jobRequest)
    {
        $this->jobRequest = $jobRequest;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'job_id' => $this->jobRequest->job_id,
            'driver_id' => $this->jobRequest->driver_id,
            'note' => $this->jobRequest->note,
            'message' => 'A driver requested your job'
        ];
    }
}
