<?php

namespace App\Services\General;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Command\Command as CommandAlias;
class TranslationService
{
    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    public static function findTranslations(Command $command, ?string $path = null): int
    {
        $basePath = $path ? base_path($path) : base_path();
        $keys = self::extractTranslationKeys($command, $basePath);

        $enJsonPath = base_path('lang/en.json');
        self::ensureLangFileExists($enJsonPath);

        $oldTranslations = json_decode(file_get_contents($enJsonPath), true) ?? [];
        $allKeys = array_merge($oldTranslations, $keys);

        // Save all keys to the English file
        file_put_contents($enJsonPath, json_encode($allKeys, JSON_PRETTY_PRINT));

        // Get locales from config
        $locales = config('translatable.locales', []);

        // Remove 'en' from locales
        $locales = array_filter($locales, fn($locale) => $locale !== 'en');

        $translationService = new self();

        foreach ($locales as $locale) {
            $localePath = base_path("lang/{$locale}.json");
            $localeTranslations = file_exists($localePath) ? json_decode(file_get_contents($localePath), true) : [];

            foreach ($allKeys as $key => $text) {
                if (!array_key_exists($key, $localeTranslations)) {
                    if (is_string($text)) {
                        $command->line("Translating key: $key to locale: $locale");
                        $localeTranslations[$key] = $translationService->apiTranslate($text, $locale);
                    } else {
                        $command->error("Key $key has non-string value, skipping translation.");
                    }
                }
            }

            file_put_contents($localePath, json_encode($localeTranslations, JSON_PRETTY_PRINT));
        }

        $command->info("All keys have been checked and translated for all locales.");
        return CommandAlias::SUCCESS;
    }

    private static function extractTranslationKeys(Command $command, string $basePath): array
    {
        $functions = [
            'trans', 'trans_choice', 'Lang::get', 'Lang::choice',
            'Lang::trans', 'Lang::transChoice', '@lang', '@choice',
            'transEditable', '__'
        ];

        $pattern = "/[^\w](?:" . implode('|', $functions) . ")\\(\\s*[\'\"](.+?)[\'\"]\\s*(?:,|\\))/";
        $finder = (new Finder())->in($basePath)->exclude(['storage', 'node_modules', 'public', 'test', 'vendor'])->name('*.php')->files();

        $keys = [];
        foreach ($finder as $file) {
            $content = $file->getContents();
            $command->info("Processing file: {$file->getRealPath()}");

            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[1] as $key) {
                    $keys[$key] = $key;
                }
            }
        }

        return $keys;
    }

    private static function ensureLangFileExists(string $enJsonPath): void
    {
        if (!file_exists($enJsonPath)) {
            self::initializeLangDirectory($enJsonPath);
        }
    }

    public function bulkTranslation(Command $command, string $locale = 'ar'): int
    {
        $enJsonPath = base_path('lang/en.json');
        if (!file_exists($enJsonPath)) {
            throw new \Exception('lang/en.json file does not exist.');
        }

        $enTranslations = json_decode(file_get_contents($enJsonPath), true);
        $translations = $this->translateKeys($command, $enTranslations, $locale);

        $localePath = base_path("lang/$locale.json");
        file_put_contents($localePath, json_encode($translations, JSON_PRETTY_PRINT));

        $command->info('Translations completed successfully.');
        return CommandAlias::SUCCESS;
    }

    private function translateKeys(Command $command, array $enTranslations, string $locale): array
    {
        $translations = [];
        foreach ($enTranslations as $key => $text) {
            $command->line("Translating: $key");
            $translations[$key] = $this->apiTranslate($text, $locale);
        }
        return $translations;
    }

    public function apiTranslate(string $text, string $locale): string
    {
        try {
            $response = $this->httpClient->get("https://api.mymemory.translated.net/get", [
                'query' => ['q' => $text, 'langpair' => "en|$locale"],
                'headers' => ['Content-type' => 'application/json']
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['responseData']['translatedText'] ?? $text;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $text;
        }
    }

    private static function initializeLangDirectory(string $enJsonPath): void
    {
        $langPath = dirname($enJsonPath);
        if (!is_dir($langPath)) {
            mkdir($langPath, 0755, true);
        }
        File::put($enJsonPath, json_encode([], JSON_PRETTY_PRINT));
    }
}
