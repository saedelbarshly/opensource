<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->nullableMorphs('model');
            $table->text('name')->nullable();
            $table->string('type')->nullable();
            $table->string('extension')->nullable();
            $table->string('size')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->string('duration')->nullable();
            $table->string('quality')->nullable();
            $table->boolean('has_thumbnail')->default(false);
            $table->boolean('has_hls')->default(false);
            $table->string('thumbnail_name')->nullable();
            $table->string('hls_name')->nullable();
            $table->string('hls_240p_name')->nullable();
            $table->string('hls_360p_name')->nullable();
            $table->string('hls_480p_name')->nullable();
            $table->string('hls_720p_name')->nullable();
            $table->string('hls_1080p_name')->nullable();
            $table->string('disk')->default('public');
            $table->string('path')->nullable();
            $table->string('option')->nullable();
            $table->boolean('is_attached')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Schema::create('media_translations', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignUuid('media_id')
        //         ->constrained('media')
        //         ->cascadeOnDelete();
        //     $table->string('alt');
        //     $table->string('locale')->index();
        //     $table->unique(['media_id', 'locale']);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
