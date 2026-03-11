<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationPing extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'name',
        'latitude',
        'longitude',
        'accuracy',
        'battery_level',
        'signal_strength',
        'microphone_status',
        'camera_status',
        'recording_status',
        'ping_timestamp',
        'received_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'float',
        'battery_level' => 'integer',
        'signal_strength' => 'integer',
        'microphone_status' => 'boolean',
        'camera_status' => 'boolean',
        'recording_status' => 'boolean',
        'ping_timestamp' => 'integer',
        'received_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
