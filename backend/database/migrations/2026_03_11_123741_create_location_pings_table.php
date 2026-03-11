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
        Schema::create('location_pings', function (Blueprint $table) {
            $table->id();
            $table->uuid('device_id');
            $table->string('name');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->float('accuracy')->nullable();
            $table->integer('battery_level')->nullable();
            $table->integer('signal_strength')->nullable();
            $table->boolean('microphone_status')->nullable();
            $table->boolean('camera_status')->nullable();
            $table->boolean('recording_status')->nullable();
            $table->bigInteger('ping_timestamp');
            $table->timestamp('received_at')->useCurrent();
            
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
            $table->index(['device_id', 'ping_timestamp']);
            $table->index('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_pings');
    }
};
