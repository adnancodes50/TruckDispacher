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
        Schema::create('config', function (Blueprint $table) {
            $table->id();

            // App settings
            $table->text('ticket_payment_url')->nullable()->comment('External URL for Pay Ticket button in app');
            $table->decimal('platform_fee_percent', 5, 2)->default(5.00)->comment('Platform fee % taken from each payment');
            $table->boolean('maintenance_mode')->default(false)->comment('1 = show maintenance screen in mobile app');
            $table->string('maintenance_message', 500)->nullable()->comment('Custom message shown during maintenance');

            // Support info
            $table->string('support_email')->nullable();
            $table->string('support_phone', 20)->nullable();

            // App version control
            $table->string('min_app_version', 20)->nullable()->comment('Minimum app version allowed — older versions blocked');

            // Timestamps
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config');
    }
};
