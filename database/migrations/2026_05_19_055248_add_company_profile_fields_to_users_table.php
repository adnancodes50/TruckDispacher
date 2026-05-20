<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable()->after('full_name');
            }

            if (!Schema::hasColumn('users', 'company_logo')) {
                $table->string('company_logo')->nullable()->after('company_name');
            }

            if (!Schema::hasColumn('users', 'business_type')) {
                $table->string('business_type')->nullable()->after('company_logo');
            }

            if (!Schema::hasColumn('users', 'mc_number')) {
                $table->string('mc_number')->nullable()->after('business_type');
            }

            if (!Schema::hasColumn('users', 'dot_number')) {
                $table->string('dot_number')->nullable()->after('mc_number');
            }

            if (!Schema::hasColumn('users', 'year_founded')) {
                $table->unsignedSmallInteger('year_founded')->nullable()->after('dot_number');
            }

            if (!Schema::hasColumn('users', 'employees')) {
                $table->string('employees')->nullable()->after('year_founded');
            }

            if (!Schema::hasColumn('users', 'service_area')) {
                $table->string('service_area')->nullable()->after('employees');
            }

            if (!Schema::hasColumn('users', 'company_description')) {
                $table->text('company_description')->nullable()->after('service_area');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'company_name',
                'company_logo',
                'business_type',
                'mc_number',
                'dot_number',
                'year_founded',
                'employees',
                'service_area',
                'company_description',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
