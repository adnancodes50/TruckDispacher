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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Links
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');

            // Invoice details
            $table->string('invoice_number', 50)->unique()->comment('e.g. INV-0001 — unique invoice number');
            $table->decimal('amount', 10, 2)->comment('Copied from job.payment_rate at invoice creation time');
            $table->timestamp('due_date')->nullable()->comment('When broker must pay');

            // Status
            $table->enum('status', ['sent', 'paid'])->default('sent');

            // Optional PDF
            $table->text('pdf_url')->nullable()->comment('Firebase Storage URL if PDF saved');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
