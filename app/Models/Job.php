<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $fillable = [
        'broker_id',
        'driver_id',
        'title',
        'description',
        'instructions',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'scheduled_at',
        'payment_rate',
        'min_rating',
        'load_type',
        'load_weight',
        'visibility',
        'status',
        'upfront_payment_status',
        'upfront_paid_at',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'upfront_paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'payment_rate' => 'decimal:2',
        'load_weight' => 'decimal:2',
        'min_rating' => 'decimal:2',
        'pickup_lat' => 'decimal:7',
        'pickup_lng' => 'decimal:7',
        'delivery_lat' => 'decimal:7',
        'delivery_lng' => 'decimal:7',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
