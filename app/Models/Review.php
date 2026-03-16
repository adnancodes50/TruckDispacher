<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'job_id',
        'driver_id',
        'broker_id',
        'punctuality',
        'safety',
        'compliance',
        'professionalism',
        'comments',
        'driver_blocked',
        'is_visible',
        'is_hidden',
        'hidden_by',
        'admin_note',
    ];

    protected $casts = [
        'driver_blocked' => 'boolean',
        'is_visible' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }
}
