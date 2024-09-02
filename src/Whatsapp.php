<?php

namespace Mrgiant\NotificationChannels;

use Mrgiant\NotificationChannels\Services\GoldenLogicWhatsapp;

class Whatsapp extends AbstractProvider
{
    public function validationRules(): array
    {
        return [
            'phone_no' => 'required',
        ];
    }

    public function data(array $input): array
    {
        return [
            'phone_no' => $input['phone_no'],
        ];
    }

    public function connect(): bool
    {
        $connect = $this->checkConnection(
            __('Congratulations! 🎉'),
            __("You've connected your Whatsapp to Golden Logic Cloud Panel")."\n"
        );

        if (! $connect) {
            return false;
        }

        return true;
    }

    public function sendMessage(string $subject, string $text): void
    {
        dispatch(function () use ($subject, $text) {
            $data = $this->notificationChannel->data;
            $GoldenLogicWhatsapp = new GoldenLogicWhatsapp($this->notificationChannel);

            if (strpos($data['phone_no'], ',') !== false) {
                $phones = explode(',', $data['phone_no']);

                foreach ($phones as $phone) {

                    $GoldenLogicWhatsapp->Send($subject."\n".$text, $phone, null, null);
                }
            } else {

                $GoldenLogicWhatsapp->Send($subject."\n".$text, $data['phone_no'], null, null);
            }

        });
    }

    private function checkConnection(string $subject, string $text): bool
    {
        $data = $this->notificationChannel->data;

        $GoldenLogicWhatsapp = new GoldenLogicWhatsapp($this->notificationChannel);

        if (strpos($data['phone_no'], ',') !== false) {
            $phones = explode(',', $data['phone_no']);

            foreach ($phones as $phone) {

                $connect = $GoldenLogicWhatsapp->Send($subject."\n".$text, $phone, null, null);
            }
        } else {

            $connect = $GoldenLogicWhatsapp->Send($subject."\n".$text, $data['phone_no'], null, null);
        }

        // $connect = $GoldenLogicWhatsapp->Send($subject . "\n" . $text, $data['phone_no'], null, null);

        return $connect === 'Yes';
    }
}
