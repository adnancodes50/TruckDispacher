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
        if (!Schema::hasTable('job_requests')) {
            Schema::create('job_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
                $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
                $table->enum('status', ['pending', 'assigned', 'rejected', 'expired', 'cancelled'])->default('pending');
                $table->text('note')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();

                $table->index('job_id');
                $table->index('driver_id');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_requests');
    }
};
