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
         Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('prefix')->nullable()->comment('This is the dashboards types like, admin, company, etc.');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('role_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('locale')->index();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unique(['role_id', 'locale']);
            $table->timestamps();
        });

         Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('prefix')->nullable()->comment('This is the dashboards types like, admin, vendor, etc.');
            $table->string('front_route_name')->nullable();
            $table->string('back_route_name')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('permission_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('locale')->index();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->unique(['permission_id', 'locale']);
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('role_id');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->unsignedBigInteger('permission_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
