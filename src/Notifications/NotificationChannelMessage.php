<?php


namespace Mrgiant\NotificationChannels\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;

class NotificationChannelMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var mixed
     */
    public $text;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $text)
    {
        $this->subject = $subject;
        $this->text = $text;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       
        return $this->markdown('notification-channels::emails.notification-channel-message', [
            'subject' => $this->subject,
            'text' => $this->text,
        ]);
    }
}
