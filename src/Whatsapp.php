<?php

namespace Mrgiant\NotificationChannels;

use Illuminate\Support\Facades\Http;

class Whatsapp extends AbstractProvider
{
    public function validationRules(): array
    {
        return [
            'phone_no'        => 'required',
            'whatsapp_token'  => 'required',
            'phone_number_id' => 'required',
            'template_name'   => 'required',
        ];
    }

    public function data(array $input): array
    {
        return [
            'phone_no'        => $input['phone_no'],
            'whatsapp_token'  => $input['whatsapp_token'] ?? '',
            'phone_number_id' => $input['phone_number_id'] ?? '',
            'template_name'   => $input['template_name'] ?? 'cloudpanel',
            'language_code'   => $input['language_code'] ?? 'en_us',
            'api_version'     => $input['api_version'] ?? 'v21.0',
        ];
    }

    public function connect(): bool
    {
        return $this->checkConnection(
            __('Congratulations! 🎉'),
            __("You've connected your Whatsapp to Golden Logic Cloud Panel") . "\n"
        );
    }

    public function sendMessage(string $subject, string $text, ?string $filePath = null): string
    {
        $data = $this->notificationChannel->data;

        $bodyParams = [$subject, $text];

        $payload = $this->buildTemplatePayload(
            $data['phone_no'],
            $data['template_name'] ?? 'cloudpanel',
            $data['language_code'] ?? 'en_us',
            $bodyParams,
            $filePath
        );

        $response = $this->sendRequest($data, $payload);

        return $response->body();
    }

    private function checkConnection(string $subject, string $text): bool
    {
        $data = $this->notificationChannel->data;

        if (empty($data['whatsapp_token']) || empty($data['phone_number_id']) || empty($data['phone_no'])) {
            return false;
        }

        $payload = $this->buildTemplatePayload(
            $data['phone_no'],
            $data['template_name'] ?? 'cloudpanel',
            $data['language_code'] ?? 'en_us',
            [$subject, $text],
            null
        );

        return $this->sendRequest($data, $payload)->ok();
    }

    private function buildTemplatePayload(
        string $to,
        string $templateName,
        string $languageCode,
        array $bodyParams = [],
        ?string $documentUrl = null,
        ?string $documentFilename = null
    ): array {
        $components = [];

        if (! empty($documentUrl)) {
            $components[] = [
                'type'       => 'header',
                'parameters' => [
                    [
                        'type'     => 'document',
                        'document' => [
                            'link'     => $documentUrl,
                            'filename' => $documentFilename ?: $this->resolveFilename($documentUrl),
                        ],
                    ],
                ],
            ];
        }

        if (! empty($bodyParams)) {
            $components[] = [
                'type'       => 'body',
                'parameters' => array_map(static fn ($value) => [
                    'type' => 'text',
                    'text' => (string) $value,
                ], array_values($bodyParams)),
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $this->normalizePhone($to),
            'type'              => 'template',
            'template'          => [
                'name'     => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];

        if (! empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $payload;
    }

    private function sendRequest(array $data, array $payload)
    {
        $version = $data['api_version'] ?? 'v21.0';
        $url     = "https://graph.facebook.com/{$version}/{$data['phone_number_id']}/messages";

        return Http::withToken($data['whatsapp_token'])
            ->acceptJson()
            ->post($url, $payload);
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone);
    }

    private function resolveFilename(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $name = basename($path);

        return $name !== '' ? $name : 'document.pdf';
    }
}
