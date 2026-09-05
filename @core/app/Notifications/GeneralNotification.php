<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class GeneralNotification extends Notification
{
    use Queueable;

    public $message;
    public $data;
    public $target_user_id;

    /**
     * Create a new notification instance.
     *
     * @param string $message
     * @param int $target_user_id
     * @param array $data (optional additional info like URL, type, etc.)
     */
    public function __construct($message, $target_user_id, $data = [])
    {
        $this->message = $message;
        $this->target_user_id = $target_user_id;
        $this->data = $data;
    }

    /**
     * Delivery channels.
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * For broadcasting (real-time).
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'user_id' => $this->target_user_id,
            'message' => $this->message,
            'data' => $this->data,
            'created_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * For database storage.
     */
    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->target_user_id,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}

?>