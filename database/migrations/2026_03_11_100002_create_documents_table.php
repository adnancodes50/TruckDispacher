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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // What job and who uploaded
            $table->foreignId('job_id')->constrained('jobs')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade')->comment('FK to users — the driver who uploaded');

            // File info
            $table->text('file_url')->comment('Firebase Storage download URL');
            $table->string('file_type', 50)->comment('pdf | image');
            $table->string('file_name')->comment('Original file name');

            // Timestamp
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
