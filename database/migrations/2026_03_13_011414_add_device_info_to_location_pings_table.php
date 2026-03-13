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
        Schema::table('location_pings', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('received_at');
            $table->string('device_model')->nullable()->after('ip_address');
            $table->string('device_brand')->nullable()->after('device_model');
            $table->string('os_version')->nullable()->after('device_brand');
            $table->string('app_version')->nullable()->after('os_version');
            $table->string('serial_number')->nullable()->after('app_version');
            $table->string('phone_number')->nullable()->after('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('location_pings', function (Blueprint $table) {
            $table->dropColumn([
                'ip_address',
                'device_model',
                'device_brand',
                'os_version',
                'app_version',
                'serial_number',
                'phone_number',
            ]);
        });
    }
};
