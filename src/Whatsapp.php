<?php

namespace Mrgiant\NotificationChannels;

use Illuminate\Support\Facades\Http;

class Whatsapp extends AbstractProvider
{
    protected ?array $lastResponse = null;
    protected ?string $lastError = null;

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    public function lastResponse(): ?array
    {
        return $this->lastResponse;
    }

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

        $components = [];

        if (! empty($filePath)) {
            $components[] = $this->documentHeader($filePath);
        }

        $bodyParams = array_values(array_filter([$subject, $text], static fn ($v) => $v !== null && $v !== ''));
        if (! empty($bodyParams)) {
            $components[] = $this->bodyComponent($bodyParams);
        }

        return $this->sendTemplate(
            $data['template_name'] ?? 'cloudpanel',
            $data['language_code'] ?? 'en_us',
            $components
        );
    }

    public function sendTemplate(string $templateName, string $languageCode, array $components = [], ?string $to = null): string
    {
        $data = $this->notificationChannel->data;

        $template = [
            'name'     => $templateName,
            'language' => ['code' => $languageCode],
        ];

        if (! empty($components)) {
            $template['components'] = $components;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $this->normalizePhone($to ?? $data['phone_no']),
            'type'              => 'template',
            'template'          => $template,
        ];

        return $this->sendRaw($payload);
    }

    public function sendText(string $message, ?string $to = null): string
    {
        $data = $this->notificationChannel->data;

        return $this->sendRaw([
            'messaging_product' => 'whatsapp',
            'to'                => $this->normalizePhone($to ?? $data['phone_no']),
            'type'              => 'text',
            'text'              => ['body' => $message],
        ]);
    }

    public function sendRaw(array $payload): string
    {
        return $this->sendRequest($payload)->body();
    }

    public function textParam(string $value): array
    {
        return ['type' => 'text', 'text' => $value];
    }

    public function headerComponent(array $parameters): array
    {
        return ['type' => 'header', 'parameters' => $parameters];
    }

    public function bodyComponent(array $values): array
    {
        return [
            'type'       => 'body',
            'parameters' => array_map(
                fn ($v) => is_array($v) ? $v : $this->textParam((string) $v),
                array_values($values)
            ),
        ];
    }

    public function buttonComponent(int $index, string $subType, array $parameters): array
    {
        return [
            'type'       => 'button',
            'sub_type'   => $subType,
            'index'      => (string) $index,
            'parameters' => $parameters,
        ];
    }

    public function documentHeader(string $url, ?string $filename = null): array
    {
        return $this->headerComponent([[
            'type'     => 'document',
            'document' => [
                'link'     => $url,
                'filename' => $filename ?: $this->resolveFilename($url),
            ],
        ]]);
    }

    public function imageHeader(string $url): array
    {
        return $this->headerComponent([[
            'type'  => 'image',
            'image' => ['link' => $url],
        ]]);
    }

    public function videoHeader(string $url): array
    {
        return $this->headerComponent([[
            'type'  => 'video',
            'video' => ['link' => $url],
        ]]);
    }

    public function locationHeader(float $latitude, float $longitude, ?string $name = null, ?string $address = null): array
    {
        $location = ['latitude' => $latitude, 'longitude' => $longitude];
        if ($name !== null)    $location['name']    = $name;
        if ($address !== null) $location['address'] = $address;

        return $this->headerComponent([[
            'type'     => 'location',
            'location' => $location,
        ]]);
    }

    private function checkConnection(string $subject, string $text): bool
    {
        $data = $this->notificationChannel->data;

        if (empty($data['whatsapp_token']) || empty($data['phone_number_id']) || empty($data['phone_no'])) {
            $this->lastError = __('Whatsapp credentials are missing (token / phone_number_id / phone_no).');
            return false;
        }

        return $this->sendRequest([
            'messaging_product' => 'whatsapp',
            'to'                => $this->normalizePhone($data['phone_no']),
            'type'              => 'template',
            'template'          => [
                'name'       => $data['template_name'] ?? 'cloudpanel',
                'language'   => ['code' => $data['language_code'] ?? 'en_us'],
                'components' => [$this->bodyComponent([$subject, $text])],
            ],
        ])->ok();
    }

    private function sendRequest(array $payload)
    {
        $this->lastError    = null;
        $this->lastResponse = null;

        $data    = $this->notificationChannel->data;
        $version = $data['api_version'] ?? 'v21.0';
        $url     = "https://graph.facebook.com/{$version}/{$data['phone_number_id']}/messages";

        try {
            $response = Http::withToken($data['whatsapp_token'])
                ->acceptJson()
                ->post($url, $payload);
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            throw $e;
        }

        $this->lastResponse = $response->json() ?? [];

        if (! $response->ok() || isset($this->lastResponse['error'])) {
            $err             = $this->lastResponse['error'] ?? [];
            $this->lastError = $err['message']
                ?? ('HTTP ' . $response->status() . ' from WhatsApp Cloud API');
        }

        return $response;
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
