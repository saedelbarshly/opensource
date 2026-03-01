<?php

namespace App\Services\General;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MicrosoftTranslationService
{


    private string $authUrl = 'https://edge.microsoft.com/translate/auth';
    private string $translateUrl = 'https://api-edge.cognitive.microsofttranslator.com/translate';

    private string $cacheKey = 'ms_translate_token';
    private string $token;


    public function __construct()
    {
        $this->token = $this->getAuthToken();
    }

    private function getAuthToken(): string
    {
        if (Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0',
            'Accept'     => '*/*',
            'Origin'     => 'https://www.bing.com',
            'Referer'    => 'https://www.bing.com/',
        ])->timeout(10)->get($this->authUrl);


        if (!$response->successful()) {
            Cache::forget($this->cacheKey);
            throw new \Exception(
                "Failed to fetch token. Status={$response->status()} Body={$response->body()}"
            );
        }

        $token = trim($response->body());
        Cache::put($this->cacheKey, $token, now()->addMinutes(9));
        return $token;
    }


    public function translate(string $text, array|string $to, string $from = 'auto'): array
    {
        $query = [
            'api-version' => '3.0',
        ];

        foreach ((array) $to as $lang) {
            $query['to'][] = $lang;
        }

        if ($from !== 'auto') {
            $query['from'] = $from;
        }

        $response = $this->withAuthRetry(function ($token) use ($query, $text) {
            return Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ])->post(
                $this->translateUrl . '?' . http_build_query($query),
                [['Text' => $text]]
            );
        });

        if (!$response->successful()) {
            throw new \Exception(
                "Translation failed. Status={$response->status()} Body={$response->body()}"
            );
        }

        return $response->json();
    }


    private function withAuthRetry(callable $callback): mixed
    {
        $response = $callback($this->token);

        if ($response->status() === 401) {
            Cache::forget($this->cacheKey);
            $this->token = $this->getAuthToken();
            $response = $callback($this->token);
        }
        return $response;
    }
}
