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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // Links
            $table->foreignId('job_id')->unique()->constrained('jobs')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');

            // 4 rating categories
            $table->unsignedTinyInteger('punctuality')->comment('1-5 stars');
            $table->unsignedTinyInteger('safety')->comment('1-5 stars');
            $table->unsignedTinyInteger('compliance')->comment('1-5 stars');
            $table->unsignedTinyInteger('professionalism')->comment('1-5 stars');

            // Comments
            $table->text('comments')->nullable();

            // Block system
            $table->boolean('driver_blocked')->default(false)->comment('Broker can flag driver — admin reviews');
            $table->boolean('is_visible')->default(true)->comment('Admin can hide a review');
            $table->text('admin_note')->nullable()->comment('Admin reason for hiding review');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('is_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
