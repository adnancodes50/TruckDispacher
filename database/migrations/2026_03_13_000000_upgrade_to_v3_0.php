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
        // 1. Update USERS table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('fcm_token')->comment('NEW — updated on every successful login');
            }
            if (!Schema::hasColumn('users', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('truck_info');
            }
            if (!Schema::hasColumn('users', 'active_requests_count')) {
                $table->unsignedTinyInteger('active_requests_count')->default(0)->after('is_available');
            }
            if (!Schema::hasColumn('users', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->nullable()->after('active_requests_count');
            }
            if (!Schema::hasColumn('users', 'total_reviews')) {
                $table->unsignedInteger('total_reviews')->default(0)->after('average_rating');
            }
            if (!Schema::hasColumn('users', 'stripe_onboarding_url')) {
                $table->text('stripe_onboarding_url')->nullable()->after('stripe_onboarding_status');
            }
            
            // Fix truck_info split if needed
            if (Schema::hasColumn('users', 'truck_info') && !Schema::hasColumn('users', 'truck_type')) {
                $table->string('truck_type', 100)->nullable()->after('license_number');
                $table->string('truck_plate', 50)->nullable()->after('truck_type');
            }

            // Update ENUM for stripe_onboarding_status if possible, or just leave as string for now to avoid issues with some DB drivers
        });

        // 2. Update JOBS table
        Schema::table('jobs', function (Blueprint $table) {
            if (Schema::hasColumn('jobs', 'date')) {
                $table->renameColumn('date', 'scheduled_at');
            }
            if (!Schema::hasColumn('jobs', 'pickup_lat')) {
                $table->decimal('pickup_lat', 10, 7)->nullable()->after('pickup_address');
                $table->decimal('pickup_lng', 10, 7)->nullable()->after('pickup_lat');
                $table->decimal('delivery_lat', 10, 7)->nullable()->after('delivery_address');
                $table->decimal('delivery_lng', 10, 7)->nullable()->after('delivery_lat');
            }
            if (!Schema::hasColumn('jobs', 'load_weight')) {
                $table->decimal('load_weight', 10, 2)->nullable()->after('load_type');
            }
            if (!Schema::hasColumn('jobs', 'min_rating')) {
                $table->decimal('min_rating', 3, 2)->default(0.00)->after('payment_rate');
            }
            if (!Schema::hasColumn('jobs', 'upfront_payment_status')) {
                $table->enum('upfront_payment_status', ['pending', 'paid', 'refunded'])->default('pending')->after('status');
                $table->timestamp('upfront_paid_at')->nullable()->after('upfront_payment_status');
            }
            if (!Schema::hasColumn('jobs', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('upfront_paid_at');
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null')->after('cancellation_reason');
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
        });

        // 3. Update JOB_REQUESTS table
        Schema::table('job_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('job_requests', 'rejection_reason')) {
                $table->string('rejection_reason', 255)->nullable()->after('responded_at');
            }
        });

        // 4. Update DOCUMENTS table
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null')->after('file_name');
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
        });

        // 5. Update PAYMENTS table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'cancellation_fee')) {
                $table->decimal('cancellation_fee', 10, 2)->nullable()->after('failure_reason');
                $table->enum('cancellation_fee_to', ['driver', 'platform', 'broker'])->nullable()->after('cancellation_fee');
            }
            if (!Schema::hasColumn('payments', 'refund_reason')) {
                $table->text('refund_reason')->nullable()->after('failure_reason');
            }
            if (Schema::hasColumn('payments', 'method')) {
                $table->dropColumn('method');
            }
            if (Schema::hasColumn('payments', 'hold_reason')) {
                $table->renameColumn('hold_reason', 'failure_reason_old'); // Keeping data just in case
            }
        });

        // 6. Update REVIEWS table
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'is_hidden')) {
                $table->boolean('is_hidden')->default(false)->after('comments');
                $table->foreignId('hidden_by')->nullable()->constrained('users')->onDelete('set null')->after('is_hidden');
                $table->index('is_hidden');
            }
        });

        // 7. Update CONFIG table
        Schema::table('config', function (Blueprint $table) {
            if (!Schema::hasColumn('config', 'max_active_requests')) {
                $table->unsignedTinyInteger('max_active_requests')->default(3)->after('support_email');
                $table->unsignedTinyInteger('request_expiry_hours')->default(2)->after('max_active_requests');
                $table->unsignedTinyInteger('payment_hold_hours')->default(24)->after('request_expiry_hours');
            }
            if (!Schema::hasColumn('config', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('payment_hold_hours');
            }
            
            // Clean up old fields
            if (Schema::hasColumn('config', 'ticket_payment_url')) {
                $table->dropColumn('ticket_payment_url');
            }
            if (Schema::hasColumn('config', 'support_phone')) {
                $table->dropColumn('support_phone');
            }
            if (Schema::hasColumn('config', 'min_app_version')) {
                $table->dropColumn('min_app_version');
            }
        });

        // 8. Update NOTIFICATIONS table
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'job_id')) {
                $table->foreignId('job_id')->nullable()->constrained('jobs')->onDelete('set null')->after('user_id');
            }
            if (!Schema::hasColumn('notifications', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });

        // 9. Create DRIVER BLOCKS table
        if (!Schema::hasTable('driver_blocks')) {
            Schema::create('driver_blocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
                $table->text('reason')->nullable();
                $table->timestamps();
                $table->unique(['broker_id', 'driver_id']);
                $table->index('broker_id');
                $table->index('driver_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_blocks');

        // Note: Reverting column additions and renames is complex and often destructive, 
        // usually we don't fully implement down() for such large upgrades unless strictly required.
    }
};
