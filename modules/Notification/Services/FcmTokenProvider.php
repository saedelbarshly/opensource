<?php

namespace Modules\Notification\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class FcmTokenProvider
{
    protected ?array $credentials = null;
    protected Client $http;
    protected string $credentialsPath;

    public function __construct(Client $http)
    {
        $this->http = $http;
        $this->credentialsPath = storage_path('app/firebase.json');
    }

    protected function loadCredentials(): void
    {
        if ($this->credentials !== null) {
            return;
        }

        if (!file_exists($this->credentialsPath)) {
            throw new RuntimeException('Firebase credentials file not found.');
        }

        $this->credentials = json_decode(file_get_contents($this->credentialsPath), true);

        if (empty($this->credentials['client_email']) || empty($this->credentials['private_key'])) {
            throw new RuntimeException('Invalid Firebase credentials file.');
        }
    }

    /**
     * Get Firebase project id
     */
    public function getProjectId(): string
    {
        $this->loadCredentials();
        return $this->credentials['project_id'];
    }

    /**
     * Get cached OAuth access token
     */
    public function getAccessToken(): string
    {
        $this->loadCredentials();
        return Cache::remember('fcm_access_token', 3500, function () {
            $jwt = $this->buildJwt();

            $response = $this->http->post(
                'https://oauth2.googleapis.com/token',
                [
                    'form_params' => [
                        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                        'assertion'  => $jwt,
                    ],
                ]
            );

            $data = json_decode($response->getBody(), true);

            if (!isset($data['access_token'])) {
                throw new RuntimeException('Failed to retrieve FCM access token.');
            }

            return $data['access_token'];
        });
    }

    /**
     * Build JWT manually (no external package)
     */
    protected function buildJwt(): string
    {
        $now = time();

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $claims = [
            'iss'   => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ];

        $base64Header = $this->base64UrlEncode(json_encode($header));
        $base64Claims = $this->base64UrlEncode(json_encode($claims));

        $signatureInput = $base64Header . '.' . $base64Claims;

        if (!openssl_sign(
            $signatureInput,
            $signature,
            $this->credentials['private_key'],
            'sha256WithRSAEncryption'
        )) {
            throw new RuntimeException('Unable to sign Firebase JWT.');
        }

        return $signatureInput . '.' . $this->base64UrlEncode($signature);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
