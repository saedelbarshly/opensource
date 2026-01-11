<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('package_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale')->nullable();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->unique(['package_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_translations');
        Schema::dropIfExists('packages');
    }
};
