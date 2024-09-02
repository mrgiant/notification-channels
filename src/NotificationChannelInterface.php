<?php

namespace Mrgiant\NotificationChannels;

interface NotificationChannelInterface
{
    public function validationRules(): array;

    public function data(array $input): array;

    public function connect(): bool;

    public function sendMessage(string $subject, string $text): void;
}
