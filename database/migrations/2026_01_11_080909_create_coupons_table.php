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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->double('value');
            $table->string('type')->nullable();
            $table->string('apply_on')->nullable();

            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->double('min_order_total')->default(0);
            $table->double('max_order_total')->default(0);

            $table->integer('limit')->default(0);
            $table->integer('limit_for_user')->default(0);
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('coupon_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('locale')->index();
            $table->unique(['coupon_id', 'locale']);
        });


        Schema::create('coupon_users', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('modelable');
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_users');
        Schema::dropIfExists('coupon_translations');
        Schema::dropIfExists('coupons');
    }
};
