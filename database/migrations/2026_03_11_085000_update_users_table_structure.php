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
        Schema::table('users', function (Blueprint $table) {
            // Rename existing name field to full_name
            if (Schema::hasColumn('users', 'name')) {
                $table->renameColumn('name', 'full_name');
            }

            // Add new fields
            $table->enum('role', ['driver', 'broker', 'admin'])->after('id');
            $table->string('company_name')->nullable()->after('full_name')->comment('Broker only');
            $table->string('phone', 20)->after('password');
            $table->string('license_number', 100)->nullable()->after('phone')->comment('Driver only');
            $table->string('truck_info')->nullable()->after('license_number')->comment('Driver only — truck type/plate');
            $table->string('stripe_account_id')->nullable()->after('truck_info')->comment('Driver Stripe Connect account ID');
            $table->string('stripe_onboarding_status', 50)->nullable()->after('stripe_account_id')->comment('pending | verified | restricted');
            $table->string('stripe_customer_id')->nullable()->after('stripe_onboarding_status')->comment('Broker Stripe Customer ID');
            $table->text('fcm_token')->nullable()->after('stripe_customer_id')->comment('Firebase Cloud Messaging token');
            $table->boolean('is_active')->default(true)->after('fcm_token')->comment('0 = deactivated by admin');

            // Drop old Firebase fields if they exist
            if (Schema::hasColumn('users', 'firebase_uid')) {
                $table->dropColumn('firebase_uid');
            }

            // Indexes
            $table->index('role');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);

            if (!Schema::hasColumn('users', 'firebase_uid')) {
                $table->string('firebase_uid')->nullable()->unique()->after('email');
            }

            $table->dropColumn([
                'role', 'company_name', 'phone', 'license_number', 'truck_info',
                'stripe_account_id', 'stripe_onboarding_status', 'stripe_customer_id',
                'fcm_token', 'is_active'
            ]);

            if (Schema::hasColumn('users', 'full_name')) {
                $table->renameColumn('full_name', 'name');
            }
        });
    }
};
