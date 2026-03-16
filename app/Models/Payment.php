<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'job_id',
        'driver_id',
        'broker_id',
        'amount',
        'platform_fee',
        'driver_amount',
        'status',
        'stripe_payment_intent_id',
        'stripe_transfer_id',
        'held_at',
        'released_at',
        'released_by',
        'failure_reason_old',
        'failure_reason',
        'cancellation_fee',
        'cancellation_fee_to',
        'refund_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'driver_amount' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
        'held_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

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

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
