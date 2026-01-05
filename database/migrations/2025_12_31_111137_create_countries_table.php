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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('phone_code', 5)->nullable();
            $table->integer('phone_length')->nullable();
            $table->integer('national_id_length')->nullable();
            $table->enum('continent', ['africa', 'europe', 'asia', 'south_america', 'north_america', 'australia'])->default('asia');
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('country_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('short_name')->nullable();
            $table->string('slug')->nullable();
            $table->string('currency')->nullable();
            $table->string('nationality')->nullable();
            $table->string('locale')->nullable();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->unique(['country_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('country_translations');
        Schema::dropIfExists('countries');
    }
};
