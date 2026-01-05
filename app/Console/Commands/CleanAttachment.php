<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Media\Models\Media;

class CleanAttachment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attachment:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to clean unusable attachment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $media = Media::where('is_attached', false)
            ->where('model_id', null)
            ->where('created_at', '<=', now()->subMinutes(30))
            ->where('model_type', '!=', '')->get();

        foreach ($media as $item) {
            $item->delete();
        }
    }
}
