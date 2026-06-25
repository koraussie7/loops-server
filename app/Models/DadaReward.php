<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DadaReward extends Model
{
    protected $fillable = [
        'profile_id',
        'video_id',
        'video_title',
        'watched_seconds',
        'reward_amount',
        'wallet_address',
        'txpowid',
        'status',
    ];

    protected $casts = [
        'watched_seconds' => 'integer',
        'reward_amount' => 'integer',
    ];
}
