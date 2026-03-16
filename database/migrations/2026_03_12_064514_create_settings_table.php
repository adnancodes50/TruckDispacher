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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            
            // API Keys & Integrations
            $table->string('stripe_secret_key')->nullable();
            $table->string('stripe_publishable_key')->nullable();
            $table->string('stripe_webhook_secret')->nullable();
            
            // Email Configuration (SMTP)
            $table->string('mail_host')->nullable();
            $table->string('mail_port')->nullable();
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_encryption')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            
            // Platform Configuration
            $table->string('platform_name')->nullable();
            $table->string('platform_email')->nullable();
            $table->string('platform_phone')->nullable();
            
            // Payment Settings
            $table->decimal('platform_commission', 5, 2)->default(5.00);
            $table->decimal('min_payout', 10, 2)->default(10.00);
            $table->decimal('max_payout', 10, 2)->nullable();
            
            // Notification Settings
            $table->boolean('push_notifications')->default(true);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            
            // App Configuration
            $table->string('android_app_version')->nullable();
            $table->string('ios_app_version')->nullable();
            
            // Terms & Policies
            $table->longText('terms_of_service')->nullable();
            $table->longText('privacy_policy')->nullable();
            
            // Maintenance
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
