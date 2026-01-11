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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid();

            $table->string('status')->nullable();
            $table->string('payment_status')->default('not_paid');
            $table->string('payment_method')->nullable();
            $table->string('transaction_code')->nullable();
            $table->string('transaction_type')->nullable();

             // price info
            $table->decimal('paid_amount', 40)->default(0);
            $table->decimal('total', 40)->default(0);
            $table->decimal('subtotal', 40)->default(0);
            $table->decimal('vendor_gross', 40)->default(0);
            $table->decimal('vendor_payout', 40)->default(0);

            $table->decimal('cash_payment_fees', 40)->default(0);
            $table->decimal('cash_fees_percentage', 40)->default(0);

            $table->decimal('commission_amount', 40)->default(0);
            $table->decimal('commission_percentage', 40)->default(0);

            $table->decimal('vat', 40)->default(0);
            $table->decimal('vat_amount', 40)->default(0);

            $table->decimal('discount', 40)->default(0);
            $table->decimal('discount_percentage', 40)->default(0);
            $table->string('discount_paid_by')->nullable();

            $table->decimal('shipping_cost', 40)->default(0);
            $table->decimal('refund_amount', 40)->default(0);
            $table->decimal('collect_amount', 40)->default(0);

            $table->string('currency')->nullable();
            $table->string('type')->nullable();
            $table->json('metadata')->nullable();
            $table->nullableMorphs('payable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
