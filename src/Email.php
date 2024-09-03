<?php

namespace Mrgiant\NotificationChannels;


use Illuminate\Support\Facades\Mail;
use Mrgiant\NotificationChannels\Notifications\NotificationChannelMessage;

class Email extends AbstractProvider
{
    public function validationRules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    public function data(array $input): array
    {
        return [
            'email' => $input['email'],
        ];
    }

    public function connect(): bool
    {
        $data = $this->notificationChannel->data;
        Mail::to($data['email'])->send(new NotificationChannelMessage(__('Congratulations! 🎉'), __("You've connected your Email to Golden Logic Cloud Panel")."\n"));
        $this->notificationChannel->connected = true;
        $this->notificationChannel->save();

        return true;
    }

    public function sendMessage(string $subject, mixed $text): void
    {
        $data = $this->notificationChannel->data;
        Mail::to($data['email'])->send(new NotificationChannelMessage($subject, $text));
    }
}
