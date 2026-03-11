<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Device extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'device_id',
        'name',
        'avatar_type',
        'avatar_value',
        'verification_code',
        'verification_code_expires_at',
        'registered_at',
        'last_seen',
        'is_active',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'last_seen' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function locationPings()
    {
        return $this->hasMany(LocationPing::class);
    }
    
    /**
     * Get the user that owns the device.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the avatar URL for the device
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar_type === 'upload' && $this->avatar_value) {
            return asset('storage/' . $this->avatar_value);
        }
        
        // Return icon emoji or default
        return null; // Will use emoji from icons.json
    }
    
    /**
     * Get avatar data for API response
     */
    public function getAvatarDataAttribute()
    {
        return [
            'type' => $this->avatar_type,
            'value' => $this->avatar_value,
            'url' => $this->avatar_url,
        ];
    }
    
    /**
     * Generate a unique 8-digit verification code
     */
    public function generateVerificationCode()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $code = '';
        
        for ($i = 0; $i < 8; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $this->verification_code = $code;
        $this->verification_code_expires_at = now()->addMinutes(30); // Expire in 30 minutes
        $this->save();
        
        return $code;
    }
    
    /**
     * Check if verification code is valid
     */
    public function isVerificationCodeValid($code)
    {
        return $this->verification_code === $code && 
               $this->verification_code_expires_at && 
               $this->verification_code_expires_at->isFuture();
    }
    
    /**
     * Clear verification code after use
     */
    public function clearVerificationCode()
    {
        $this->verification_code = null;
        $this->verification_code_expires_at = null;
        $this->save();
    }
}
