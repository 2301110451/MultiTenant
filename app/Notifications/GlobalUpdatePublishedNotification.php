<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GlobalUpdatePublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $version,
        private readonly string $description,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Platform Update {$this->version}: {$this->title}")
            ->greeting('Hello!')
            ->line("A new platform update is available: {$this->title}")
            ->line("Version: {$this->version}")
            ->line($this->description)
            ->line('Please check your tenant update feed for full details.');
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'version' => $this->version,
            'description' => $this->description,
        ];
    }
}
