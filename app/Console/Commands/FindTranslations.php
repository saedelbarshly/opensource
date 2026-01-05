<?php

namespace App\Console\Commands;

use App\Services\General\TranslationService;
use Illuminate\Console\Command;

class FindTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:find {directory=app}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'search about translations keys in files ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $directory = $this->argument('directory');
        return TranslationService::findTranslations($this, $directory);
    }
}
