<?php

use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->double('discount')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->boolean('is_returnable')->default(1);
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('locale')->nullable();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unique(['product_id', 'locale']);
        });

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
    }
};
