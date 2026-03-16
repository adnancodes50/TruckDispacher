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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();

            // Who is involved
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade')->comment('FK to users — broker who posted this job');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null')->comment('FK to users — null until driver accepts');

            // Job details
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable()->comment('Only shown to driver AFTER they accept');

            // Location
            $table->string('pickup_address', 500);
            $table->string('delivery_address', 500);

            // Schedule and payment
            $table->dateTime('date');
            $table->decimal('payment_rate', 10, 2)->comment('How much driver earns for this job');
            $table->string('load_type', 100)->comment('e.g. Dry Van, Flatbed, Reefer');

            // Visibility
            $table->enum('visibility', ['public', 'assigned'])->default('public')->comment('public = any driver can see | assigned = specific driver only');

            // Job status lifecycle
            $table->enum('status', ['open', 'assigned', 'in_progress', 'completed', 'cancelled'])->default('open');

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('visibility');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
