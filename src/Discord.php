<?php

namespace Mrgiant\NotificationChannels;

use Illuminate\Support\Facades\Http;

class Discord extends AbstractProvider
{
    public function validationRules(): array
    {
        return [
            'webhook_url' => 'required|url',
        ];
    }

    public function data(array $input): array
    {
        return [
            'webhook_url' => $input['webhook_url'],
        ];
    }

    public function connect(): bool
    {
        $connect = $this->checkConnection(
            __('Congratulations! 🎉'),
            __("You've connected your Discord to Golden Logic Cloud Panel")."\n"
        );

        if (! $connect) {
            return false;
        }

        return true;
    }

    public function sendMessage(string $subject, string $text): string
    {
       
            $data = $this->notificationChannel->data;
            $connect= Http::post($data['webhook_url'], [
                'content' => '*'.$subject.'*'."\n".$text,
            ]);

            return $connect->body();
       
    }

    private function checkConnection(string $subject, string $text): bool
    {
        $data = $this->notificationChannel->data;
        $connect = Http::post($data['webhook_url'], [
            'content' => '*'.$subject.'*'."\n".$text,
        ]);

        return $connect->successful();
    }
}
