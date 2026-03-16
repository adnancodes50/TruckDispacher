<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Handle toggles before validation
        $request->merge([
            'push_notifications' => $request->has('push_notifications'),
            'email_notifications' => $request->has('email_notifications'),
            'sms_notifications' => $request->has('sms_notifications'),
            'maintenance_mode' => $request->has('maintenance_mode'),
        ]);

        $validatedData = $request->validate([
            // API Keys & Integrations
            'stripe_secret_key' => 'nullable|string',
            'google_maps_api_key' => 'nullable|string',
            'stripe_publishable_key' => 'nullable|string',
            'stripe_webhook_secret' => 'nullable|string',

            // Email Configuration (SMTP)
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|string',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',

            // Platform Configuration
            'platform_name' => 'nullable|string',
            'platform_email' => 'nullable|email',
            'platform_phone' => 'nullable|string',

            // Payment Settings
            'platform_commission' => 'nullable|numeric|min:0|max:100',
            'min_payout' => 'nullable|numeric|min:0',
            'max_payout' => 'nullable|numeric|min:0',

            // Notification Settings
            'push_notifications' => 'nullable|boolean',
            'email_notifications' => 'nullable|boolean',
            'sms_notifications' => 'nullable|boolean',

            // App Configuration
            'android_app_version' => 'nullable|string',
            'ios_app_version' => 'nullable|string',

            // Terms & Policies
            'terms_of_service' => 'nullable|string',
            'privacy_policy' => 'nullable|string',

            // Maintenance
            'maintenance_mode' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string',
        ]);

        Setting::updateOrCreate(['id' => 1], $validatedData);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
