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

            // Links
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade')->comment('Denormalised for easier reporting');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');

            // Money
            $table->decimal('amount', 10, 2)->comment('Total charged to broker');
            $table->decimal('platform_fee', 10, 2)->comment('Fee kept by platform');
            $table->decimal('driver_amount', 10, 2)->comment('Amount sent to driver = amount - platform_fee');

            // How paid
            $table->enum('method', ['card', 'ach'])->comment('card = credit/debit | ach = bank transfer');

            // Status
            $table->enum('status', ['pending', 'processing', 'held', 'paid', 'failed', 'refunded'])->default('pending');

            // Stripe IDs
            $table->string('stripe_payment_intent_id')->nullable()->comment('Stripe PaymentIntent ID — for audit');
            $table->string('stripe_transfer_id')->nullable()->comment('Stripe Transfer ID — payout to driver');

            // Hold system
            $table->timestamp('held_at')->nullable()->comment('When payment was put on hold');
            $table->timestamp('released_at')->nullable()->comment('When hold was released');
            $table->foreignId('released_by')->nullable()->constrained('users')->onDelete('set null')->comment('Admin user ID who released hold');
            $table->text('hold_reason')->nullable();
            $table->text('failure_reason')->nullable()->comment('Why payment failed');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('created_at');
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
