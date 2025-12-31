<?php

namespace Modules\Media\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModelService
{
    protected string $modelsPath;

    protected array $excludedModels = [
        'AppMedia',
    ];

    public function __construct()
    {
        $this->modelsPath = app_path('Models');
    }

    /**
     * Get all discovered models with metadata
     */
    public function all(): array
    {
        if (!File::isDirectory($this->modelsPath)) {
            return [];
        }

        return collect(File::allFiles($this->modelsPath))
            ->map(fn($file) => $this->resolveModelData($file->getPathname()))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get model class names only
     */
    public function modelNames(): array
    {
        return array_column($this->all(), 'model');
    }

    /**
     * Get readable module titles
     */
    public function moduleTitles(): array
    {
        return array_column($this->all(), 'title');
    }

    /**
     * Resolve model information from file path
     */
    protected function resolveModelData(string $filePath): ?array
    {
        $class = $this->classFromPath($filePath);

        if (!$class || $this->shouldSkip($class) || !$this->isEloquentModel($class)) {
            return null;
        }

        $name = class_basename($class);

        return [
            'model' => $name,
            'title' => Str::headline($name),
            'fqcn'  => $class,
        ];
    }

    /**
     * Convert file path to FQCN
     */
    protected function classFromPath(string $filePath): ?string
    {
        $relative = str_replace(
            [app_path() . DIRECTORY_SEPARATOR, '.php'],
            ['', ''],
            $filePath
        );

        return 'App\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relative);
    }

    /**
     * Skip excluded or translation models
     */
    protected function shouldSkip(string $class): bool
    {
        $name = class_basename($class);

        return in_array($name, $this->excludedModels, true)
            || str_ends_with($name, 'Translation');
    }

    /**
     * Ensure class is an Eloquent model
     */
    protected function isEloquentModel(string $class): bool
    {
        return class_exists($class)
            && is_subclass_of($class, Model::class);
    }
}
