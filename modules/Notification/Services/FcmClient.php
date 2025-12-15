<?php

namespace Modules\Notification\Services;

use GuzzleHttp\Client;
use RuntimeException;

class FcmClient
{
    protected Client $http;
    protected FcmTokenProvider $tokenProvider;
    protected string $projectId;

    public function __construct(
        Client $http,
        FcmTokenProvider $tokenProvider
    ) {
        $this->http = $http;
        $this->tokenProvider = $tokenProvider;
        $this->projectId = $tokenProvider->getProjectId();
    }

    /**
     * Send FCM message
     */
    public function send(array $payload): bool
    {
        $response = $this->http->post(
            "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->tokenProvider->getAccessToken(),
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 5,
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException(
                'FCM request failed with status ' . $response->getStatusCode()
            );
        }

        return true;
    }
}
