<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // View: Active jobs with broker and driver names
        DB::statement("
            CREATE OR REPLACE VIEW view_active_jobs AS
            SELECT
                j.id,
                j.title,
                j.status,
                j.payment_rate,
                j.load_type,
                j.pickup_address,
                j.delivery_address,
                j.date,
                j.visibility,
                b.full_name      AS broker_name,
                b.company_name   AS broker_company,
                d.full_name      AS driver_name,
                d.phone          AS driver_phone,
                j.created_at
            FROM jobs j
            LEFT JOIN users b ON b.id = j.broker_id
            LEFT JOIN users d ON d.id = j.driver_id
            WHERE j.status NOT IN ('cancelled');
        ");

        // View: Payment summary
        DB::statement("
            CREATE OR REPLACE VIEW view_payment_summary AS
            SELECT
                p.id,
                p.amount,
                p.platform_fee,
                p.driver_amount,
                p.method,
                p.status,
                p.created_at,
                d.full_name      AS driver_name,
                d.email          AS driver_email,
                b.full_name      AS broker_name,
                b.company_name   AS broker_company,
                j.title          AS job_title,
                i.invoice_number
            FROM payments p
            JOIN users d    ON d.id = p.driver_id
            JOIN users b    ON b.id = p.broker_id
            JOIN jobs j     ON j.id = p.job_id
            JOIN invoices i ON i.id = p.invoice_id;
        ");

        // View: Driver stats (for admin dashboard)
        DB::statement("
            CREATE OR REPLACE VIEW view_driver_stats AS
            SELECT
                u.id,
                u.full_name,
                u.email,
                u.phone,
                u.stripe_onboarding_status,
                u.is_active,
                COUNT(DISTINCT j.id)   AS total_jobs,
                COUNT(DISTINCT r.id)   AS total_reviews,
                ROUND(AVG(
                    (IFNULL(r.punctuality, 0) + IFNULL(r.safety, 0) + IFNULL(r.compliance, 0) + IFNULL(r.professionalism, 0)) / 4
                ), 2)                  AS avg_rating,
                SUM(p.driver_amount)   AS total_earned,
                u.created_at
            FROM users u
            LEFT JOIN jobs j        ON j.driver_id  = u.id AND j.status = 'completed'
            LEFT JOIN reviews r     ON r.driver_id  = u.id AND r.is_visible = 1
            LEFT JOIN payments p    ON p.driver_id  = u.id AND p.status = 'paid'
            WHERE u.role = 'driver'
            GROUP BY u.id, u.full_name, u.email, u.phone, u.stripe_onboarding_status, u.is_active, u.created_at;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_active_jobs");
        DB::statement("DROP VIEW IF EXISTS view_payment_summary");
        DB::statement("DROP VIEW IF EXISTS view_driver_stats");
    }
};
