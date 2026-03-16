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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Who gets this notification
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // What kind
            $table->string('type', 100)->comment('job_assigned | job_rejected | job_completed | invoice_sent | payment_received | review_submitted');

            // Content
            $table->string('title');
            $table->text('body');

            // Extra data for deep linking
            $table->json('data')->nullable()->comment('e.g. {"job_id": 12}');

            // Read status
            $table->timestamp('read_at')->nullable()->comment('NULL means unread');

            // Timestamp
            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->index('read_at');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
