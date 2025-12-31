<?php

namespace Modules\Notification\Broadcasting\Payloads;

interface FcmPayloadContract
{
    public static function build(string $token, array $data): array;
}
