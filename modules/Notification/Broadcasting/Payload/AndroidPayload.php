<?php
namespace Modules\Notification\Broadcasting\Payloads;

class AndroidPayload implements FcmPayloadContract
{
    public static function build(string $token, array $data): array
    {
        return [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $data['title'] ?? '',
                    'body'  => $data['body'] ?? '',
                ],
                'data' => self::stringify($data),
            ],
        ];
    }

    protected static function stringify(array $data): array
    {
        return collect($data)->map(fn ($v) =>
            is_scalar($v) ? (string)$v : json_encode($v)
        )->toArray();
    }
}
