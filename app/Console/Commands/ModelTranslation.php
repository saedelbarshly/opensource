<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ModelTranslation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:model-translation {name : The name of the model translation class}
                        {--m : Create a migration file for the model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new model translation class using a model_translate stub';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Convert the name to StudlyCase if it isn't already
        $className = Str::studly($name);
        $translationModelName = $className . 'Translation';

        // Get the stub paths
        $stubPath = base_path('stubs/custom_model.stub');
        $translationStubPath = base_path('stubs/custom_model_translation.stub');
        $migrationStubPath = base_path('stubs/custom_migration.stub');

        // Check if the stubs exist
        if (!File::exists($stubPath)) {
            $this->error('Stub file not found at: ' . $stubPath);
            return Command::FAILURE;
        }

        if (!File::exists($translationStubPath)) {
            $this->error('Translation stub file not found at: ' . $translationStubPath);
            return Command::FAILURE;
        }

        if (!File::exists($migrationStubPath)) {
            $this->error('Migration stub file not found at: ' . $migrationStubPath);
            return Command::FAILURE;
        }

        // Get the stub content
        $stub = File::get($stubPath);
        $transStub = File::get($translationStubPath);

        // Create the Models directory if it doesn't exist
        $modelDirectory = app_path('Models');
        if (!File::isDirectory($modelDirectory)) {
            File::makeDirectory($modelDirectory, 0755, true);
        }

        // Create main model
        $this->createMainModel($modelDirectory, $className, $stub);

        // Create translation model
        $this->createTranslationModel($modelDirectory, $className, $translationModelName, $transStub);

        // Create migration if requested
        if ($this->option('m')) {
            $this->createMigration($className, $migrationStubPath);
        } else {
            $this->info('Migration creation skipped (use --m to create).');
        }

        return Command::SUCCESS;
    }

    /**
     * Create the main model file
     *
     * @param string $modelDirectory
     * @param string $className
     * @param string $stub
     * @return int
     */
    protected function createMainModel($modelDirectory, $className, $stub)
    {
        // Prepare replacement values for main model
        $replacements = [
            '{{ namespace }}' => 'App\\Models',
            '{{ modelName }}' => $className,
            '{{ modelSmall }}' => Str::lower($className),
        ];

        // Replace the stub variables
        foreach ($replacements as $key => $value) {
            $stub = str_replace($key, $value, $stub);
        }

        // Generate the model file path
        $modelPath = $modelDirectory . '/' . $className . '.php';

        // Check if the model file already exists
        if (File::exists($modelPath)) {
            $this->error('Model already exists!');
            return Command::FAILURE;
        }

        // Create the model file
        File::put($modelPath, $stub);

        $this->info('Main model created successfully.');
    }

    /**
     * Create the translation model file
     *
     * @param string $modelDirectory
     * @param string $className
     * @param string $translationModelName
     * @param string $transStub
     * @return int
     */
    protected function createTranslationModel($modelDirectory, $className, $translationModelName, $transStub)
    {
        // Prepare replacement values for translation model
        $translationReplacements = [
            '{{ namespace }}' => 'App\\Models',
            '{{ class }}' => $translationModelName,
            '{{ parentModel }}' => $className,
        ];

        // Replace the translation stub variables
        foreach ($translationReplacements as $key => $value) {
            $transStub = str_replace($key, $value, $transStub);
        }

        // Generate the translation model file path
        $translationModelPath = $modelDirectory . '/' . $translationModelName . '.php';

        // Check if the translation model file already exists
        if (File::exists($translationModelPath)) {
            $this->error('Translation model already exists!');
            return Command::FAILURE;
        }

        // Create the translation model file
        File::put($translationModelPath, $transStub);

        $this->info('Translation model created successfully.');
    }

    /**
     * Create a new migration file.
     *
     * @param string $className
     * @param string $stubPath
     * @return void
     */
    protected function createMigration($className, $stubPath)
    {
        $tableName = Str::plural(Str::snake($className));
        $tableWithoutS = Str::singular($tableName);
        $translationsTable = $tableWithoutS . '_translations';
        $foreignKey = $tableWithoutS . '_id';

        $migrationPath = database_path('migrations/' . date('Y_m_d_His') . '_create_' . $tableName . '_table.php');

        $stub = File::get($stubPath);

        $replacements = [
            '{{ tableName }}' => $tableName,
            '{{ translationsTable }}' => $translationsTable,
            '{{ foreignKey }}' => $foreignKey
        ];

        foreach ($replacements as $key => $value) {
            $stub = str_replace($key, $value, $stub);
        }

        File::put($migrationPath, $stub);

        $this->info('Migration created successfully.');
    }
}
