<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'config'; // Schema uses 'config' instead of 'configs'

    public $timestamps = false; // Schema only has updated_at

    protected $fillable = [
        'ticket_payment_url',
        'platform_fee_percent',
        'maintenance_mode',
        'maintenance_message',
        'support_email',
        'support_phone',
        'min_app_version',
        'google_maps_api_key',
        'updated_at',
    ];

    protected $casts = [
        'platform_fee_percent' => 'decimal:2',
        'maintenance_mode' => 'boolean',
    ];
}
