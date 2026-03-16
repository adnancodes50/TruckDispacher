<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'stripe_secret_key',
        'stripe_publishable_key',
        'stripe_webhook_secret',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'platform_name',
        'platform_email',
        'platform_phone',
        'platform_commission',
        'min_payout',
        'max_payout',
        'push_notifications',
        'email_notifications',
        'sms_notifications',
        'android_app_version',
        'ios_app_version',
        'google_maps_api_key',
        'terms_of_service',
        'privacy_policy',
        'maintenance_mode',
        'maintenance_message',
    ];

    protected $casts = [
        'push_notifications' => 'boolean',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'platform_commission' => 'decimal:2',
        'min_payout' => 'decimal:2',
        'max_payout' => 'decimal:2',
        'maintenance_mode' => 'boolean',
    ];
}
