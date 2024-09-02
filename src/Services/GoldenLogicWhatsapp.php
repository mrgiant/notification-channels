<?php

namespace Mrgiant\NotificationChannels\Services;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GoldenLogicWhatsapp
{

   private $setting_general;


    public function __construct($setting_general)
    {

        $this->setting_general = $setting_general;
        
    }
    public function Send($Message, $MobileNo, $FileUrl, $FileName)
    {
        

        if ($this->setting_general) {

            if ($this->setting_general->Whatsapp_Api_Key != '') {

                if (! $this->CheckSessionWhatsapp($this->setting_general->Whatsapp_Api_Key, $this->setting_general->Whatsapp_Device_Id, $this->setting_general->Whatsapp_Host)) {

                    return 'Not Sended';
                }

                if (empty($FileUrl)) {
                    return $this->sendWhatsapp($this->setting_general->Whatsapp_Api_Key, $this->setting_general->Whatsapp_Device_Id, $this->setting_general->Whatsapp_Host, $MobileNo, $Message);

                } else {
                    return $this->sendWhatsappWithFile($this->setting_general->Whatsapp_Api_Key, $this->setting_general->Whatsapp_Device_Id, $this->setting_general->Whatsapp_Host, $MobileNo, $Message, $FileUrl, $FileName);

                }

            }
        }

        return '';
    }

    public function sendWhatsapp($api_key, $Device_Id, $Whatsapp_Host, $MobileNo, $message)
    {

        $MobileNo_value = '968'.substr($MobileNo, -8);

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $body = [
            'receiver' => $MobileNo_value,
            'delay' => '0',
            'message' => [
                'text' => $message,
            ],
        ];

        $response = Http::withHeaders($headers)
            ->post(''.$Whatsapp_Host.'/chats/send?id='.$Device_Id.'&api_key='.$api_key.'', $body);

        if ($response->ok()) {
            return 'Yes';
        } else {

            return 'No';
        }

        // return $response->body();

    }

    public function sendWhatsappWithFile($api_key, $Device_Id, $Whatsapp_Host, $MobileNo, $message, $FileUrl, $FileName)
    {

        $MobileNo_value = '968'.substr($MobileNo, -8);

        // $explode=explode('.', $FileUrl);

        /*
            $file_type=strtolower(end($explode));
            $extentions=[
                'jpg'=>'image',
                'jpeg'=>'image',
                'png'=>'image',
                'webp'=>'image',
                'pdf'=>'document',
                'docx'=>'document',
                'xlsx'=>'document',
                'csv'=>'document',
                'txt'=>'document'
            ];

        */

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $body = [
            'receiver' => $MobileNo_value,
            'delay' => '0',
            'message' => [
                'caption' => $message,
                'mimetype' => $this->get_mime_type_from_filename($FileName),
                'document' => [
                    'url' => asset($FileUrl),
                ],
            ],

        ];

        $response = Http::withHeaders($headers)
            ->post(''.$Whatsapp_Host.'/chats/send?id='.$Device_Id.'&api_key='.$api_key.'', $body);

        Storage::disk('public')->delete($FileName);
        if ($response->ok()) {
            return 'Yes';
        } else {

            return 'No';
        }

        // return $response->body();

    }

    public function get_mime_type_from_filename($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        switch ($extension) {
            // Images
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            case 'bmp':
                return 'image/bmp';
            case 'webp':
                return 'image/webp';
            case 'svg':
                return 'image/svg+xml';

                // Videos
            case 'mp4':
                return 'video/mp4';
            case 'webm':
                return 'video/webm';
            case 'ogg':
                return 'video/ogg';

                // Documents
            case 'pdf':
                return 'application/pdf';
            case 'doc':
            case 'docx':
                return 'application/msword';
            case 'xls':
            case 'xlsx':
                return 'application/vnd.ms-excel';
            case 'ppt':
            case 'pptx':
                return 'application/vnd.ms-powerpoint';
            case 'odt':
                return 'application/vnd.oasis.opendocument.text';
            case 'ods':
                return 'application/vnd.oasis.opendocument.spreadsheet';
            case 'odp':
                return 'application/vnd.oasis.opendocument.presentation';

                // Audio
            case 'mp3':
                return 'audio/mpeg';
            case 'wav':
                return 'audio/wav';
            case 'ogg':
                return 'audio/ogg';

                // Other file types
            default:
                return 'application/octet-stream'; // fallback MIME type for unknown file types
        }
    }

    public function CheckSessionWhatsapp($api_key, $Device_Id, $Whatsapp_Host)
    {

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->get('https://whatsapp.golden-logic.com/sessions/status/'.$Device_Id.'?api_key='.$api_key);

        $data = $response->json()['data'];

        if (! empty($data['valid_session'])) {
            if ($data['valid_session']) {

                return true;
            }
        }

        return false;

    }
}
