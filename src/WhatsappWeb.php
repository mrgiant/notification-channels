<?php

namespace Mrgiant\NotificationChannels;

use Mrgiant\NotificationChannels\Services\GoldenLogicWhatsapp;

class WhatsappWeb extends AbstractProvider
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
            'Whatsapp_Api_Key' => "",
            'Whatsapp_Device_Id' => "",
            'Whatsapp_Host' => "",

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

    public function sendMessage(string $subject, string $text,?string $fileURL=null): string
    {
        
            $data = $this->notificationChannel->data;
            $GoldenLogicWhatsapp = new GoldenLogicWhatsapp($this->notificationChannel->data);

           

            return $GoldenLogicWhatsapp->Send($subject."\n".$text, $data['phone_no'], $fileURL);
            

            return "";

       
    }

    private function checkConnection(string $subject, string $text): bool
    {
        $data = $this->notificationChannel->data;

        $GoldenLogicWhatsapp = new GoldenLogicWhatsapp($this->notificationChannel->data);

       

            $connect = $GoldenLogicWhatsapp->Send($subject."\n".$text, $data['phone_no'], null, null);
        

        // $connect = $GoldenLogicWhatsapp->Send($subject . "\n" . $text, $data['phone_no'], null, null);

        return $connect === 'Yes';
    }
}
