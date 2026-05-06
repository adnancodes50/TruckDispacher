<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stripe\Customer;
use Stripe\Stripe;

class AppAuthController extends Controller
{
    private function configureMailFromSettings(Setting $settings): void
    {
        // ✅ REQUIRE settings - no defaults
        if (! $settings || ! $settings->mail_host || ! $settings->mail_port) {
            throw new \Exception('Email configuration not found in settings table');
        }

        // ✅ Set mail driver and SMTP configuration from database ONLY
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $settings->mail_host);
        Config::set('mail.mailers.smtp.port', (int) $settings->mail_port);
        Config::set('mail.mailers.smtp.username', $settings->mail_username);
        Config::set('mail.mailers.smtp.password', $settings->mail_password);
        Config::set('mail.mailers.smtp.encryption', $settings->mail_encryption ?? 'tls');
        Config::set('mail.from.address', $settings->mail_from_address);
        Config::set('mail.from.name', $settings->mail_from_name ?? $settings->platform_name ?? 'TruckDispatcher');

        // ✅ Force Laravel to rebuild the mailer with new config
        app('mail.manager')->forgetMailers();

        // ✅ Log configuration for debugging
        \Log::debug('✅ Mail configured from database settings:', [
            'driver' => 'smtp',
            'host' => $settings->mail_host,
            'port' => $settings->mail_port,
            'from' => $settings->mail_from_address,
        ]);
    }

    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
        'player_id' => 'nullable|string', // ✅ added
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors(),
        ], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (! $user) {
        return response()->json([
            'status' => false,
            'message' => 'User not found',
        ], 404);
    }

    // admin not allowed
    if ($user->role === 'admin') {
        return response()->json([
            'status' => false,
            'message' => 'Admin cannot login',
        ], 403);
    }

    if (! Hash::check($request->password, $user->password)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid password',
        ], 401);
    }

    // ✅ Create token
    $token = $user->createToken('auth_token')->plainTextToken;

    // ✅ Save OneSignal Player ID (if provided)
    if ($request->player_id) {
        $user->onesignal_player_id = $request->player_id;
        $user->save();

        \Log::info('OneSignal Player ID saved on login', [
            'user_id' => $user->id,
            'player_id' => $request->player_id
        ]);
    } else {
        \Log::warning('Login without Player ID', [
            'user_id' => $user->id
        ]);
    }

    // base response
   $data = [
    'id' => $user->id,
    'status' => true,
    'message' => 'Login successful',
    'token' => $token,
    'email' => $user->email,
    'role' => $user->role,
    'player_id' => $user->onesignal_player_id, // ✅ ADD THIS
];

    // broker response
    if ($user->role === 'broker') {
        $data['name'] = $user->full_name;
        $data['company_name'] = $user->company_name;
    }

    // driver response
    if ($user->role === 'driver') {
        $data['name'] = $user->full_name;
        $data['truck_info'] = $user->truck_info;
    }

    return response()->json($data);
}

    // broker registerration api method

    public function registerBroker(Request $request)
    {
        // ✅ 1. Validate
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // ✅ 2. Get Stripe key from settings
        $settings = Setting::first();

        if (! $settings || ! $settings->stripe_secret_key) {
            return response()->json([
                'status' => false,
                'message' => 'Stripe is not configured. Please contact admin.',
            ], 500);
        }

        Stripe::setApiKey($settings->stripe_secret_key);

        try {
            // ✅ 3. Create Stripe Customer FIRST
            $customer = Customer::create([
                'email' => $request->email,
                'name' => $request->company_name ?? $request->full_name,
                'phone' => $request->phone,
            ]);

            // ✅ 4. Create user with stripe_customer_id
            $user = User::create([
                'role' => 'broker',
                'full_name' => $request->full_name,
                'company_name' => $request->company_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'stripe_customer_id' => $customer->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Stripe customer creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }

        // ✅ 5. Response
        return response()->json([
            'status' => true,
            'message' => 'Registered successfully',
            'data' => $user,
        ]);
    }

    public function registerDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|min:6|confirmed',
            'license_number' => 'required|string|max:255',

            // OPTIONAL FIELDS 👇
            'truck_info' => 'nullable|string|max:255',
            'truck_type' => 'nullable|string|max:255',
            'truck_plate' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $driver = User::create([
            'role' => 'driver',
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'license_number' => $request->license_number,

            // OPTIONAL (will store null if not provided)
            'truck_info' => $request->truck_info,
            'truck_type' => $request->truck_type,
            'truck_plate' => $request->truck_plate,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Driver registered successfully',
            'email' => $driver->email,
            'role' => $driver->role,
            'name' => $driver->full_name,
            'truck_info' => $driver->truck_info,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout successful',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // ✅ Find broker or driver only (not admin)
        $user = User::where('email', $request->email)
            ->whereIn('role', ['broker', 'driver'])
            ->first();

        // Always return 200 to avoid email enumeration attacks
        if (! $user) {
            return response()->json([
                'status' => true,
                'message' => 'If this email is registered, a reset link has been sent.',
                // 'user' => $user,
            ]);
        }

        // ✅ Load mail settings (REQUIRED - no defaults)
        $settings = Setting::first();

        if (! $settings || ! $settings->mail_host) {
            \Log::error('❌ CRITICAL: Mail settings not configured in database');

            return response()->json([
                'status' => false,
                'message' => 'EMAIL CONFIGURATION ERROR',
                'error' => 'Mail settings not found in database settings table',
                'required_fields' => [
                    'mail_host' => 'eg: sandbox.smtp.mailtrap.io',
                    'mail_port' => 'eg: 2525',
                    'mail_username' => 'from Mailtrap',
                    'mail_password' => 'from Mailtrap',
                    'mail_from_address' => 'eg: noreply@app.com',
                ],
            ], 500);
        }

        try {
            // ✅ Configure mail before sending
            \Log::info('🔧 Configuring mail from database settings...');
            $this->configureMailFromSettings($settings);

            // ✅ Delete old tokens for this email
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // ✅ Generate new token
            $token = Str::random(64);

            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]);

            $platformName = ($settings && $settings->platform_name) ? $settings->platform_name : 'TruckDispatcher';
            $resetLink = url("/reset-password?token={$token}&email={$user->email}");

            $htmlBody = "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:24px;border:1px solid #e5e7eb;border-radius:8px;'>
                    <h2 style='color:#1f2937;'>Password Reset Request</h2>
                    <p style='color:#4b5563;'>Hi <strong>{$user->full_name}</strong>,</p>
                    <p style='color:#4b5563;'>We received a request to reset your password.</p>
                    <p style='margin:24px 0;'>
                        <a href='{$resetLink}'
                           style='background:#2563eb;color:#ffffff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;'>
                            Reset My Password
                        </a>
                    </p>
                    <p style='color:#6b7280;font-size:13px;'>This link expires in <strong>60 minutes</strong>.</p>
                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:24px 0;'>
                    <p style='color:#9ca3af;font-size:12px;'>— The {$platformName} Team</p>
                </div>
            ";

            // ✅ Send email with STRICT error handling
            $emailSent = false;
            try {
                \Log::info('📧 Attempting to send reset email to: '.$user->email);

                Mail::send([], [], function ($message) use ($user, $platformName, $htmlBody) {
                    $message->to($user->email, $user->full_name)
                        ->subject("Password Reset — {$platformName}")
                        ->html($htmlBody);
                });

                $emailSent = true;
                \Log::info('✅ SUCCESS: Reset email sent to: '.$user->email);

            } catch (\Throwable $mailException) {
                // ✅ Log EVERYTHING about the error
                \Log::error('❌ MAIL FAILED: Could not send email', [
                    'exception_type' => get_class($mailException),
                    'exception_message' => $mailException->getMessage(),
                    'file' => $mailException->getFile(),
                    'line' => $mailException->getLine(),
                    'trace' => $mailException->getTraceAsString(),
                    'to_email' => $user->email,
                    'mail_config' => [
                        'driver' => config('mail.default'),
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port'),
                        'from' => config('mail.from.address'),
                    ],
                ]);

                // ✅ Delete the token since email failed
                DB::table('password_reset_tokens')->where('email', $request->email)->delete();

                return response()->json([
                    'status' => false,
                    'message' => 'FAILED TO SEND EMAIL',
                    'error' => $mailException->getMessage(),
                    'exception_type' => get_class($mailException),
                    'mail_settings_being_used' => [
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port'),
                        'from' => config('mail.from.address'),
                    ],
                    'next_steps' => [
                        '1. Check Laravel logs at: storage/logs/laravel.log',
                        '2. Check Mailtrap.io inbox for emails or errors',
                        '3. Verify database settings match Mailtrap credentials',
                    ],
                ], 500);
            }

            if (! $emailSent) {
                \Log::error('❌ Email sent but no confirmation received');
                DB::table('password_reset_tokens')->where('email', $request->email)->delete();

                return response()->json([
                    'status' => false,
                    'message' => 'Email send failed - no confirmation from mail service',
                ], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'Password reset email sent successfully. Check your inbox.',
                'debug_info' => [
                    'email_sent_to' => $user->email,
                    'timestamp' => now()->toIso8601String(),
                    'using_defaults' => ! ($settings && $settings->mail_host) ? 'yes' : 'no',
                ],
            ]);

        } catch (\Throwable $e) {
            // ✅ Catch any unexpected errors
            \Log::error('❌ UNEXPECTED ERROR in forgotPassword:', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Unexpected error occurred',
                'error' => $e->getMessage(),
                'check_logs' => 'storage/logs/laravel.log',
            ], 500);
        }
    }

    // ─────────────────────────────────────────────
    // ✅ TEST EMAIL CONFIGURATION (for debugging)
    // GET /api/test-mail-config
    // ─────────────────────────────────────────────

    // ─────────────────────────────────────────────
    // ✅ TEST SEND EMAIL (for debugging)
    // POST /api/test-send-email
    // Body: { "email": "test@example.com" }
    // ─────────────────────────────────────────────

    // ─────────────────────────────────────────────
    // GET /api/reset-password?email=...&token=... (from email link)
    // POST /api/reset-password (to reset password)
    // Body: { "email": "...", "token": "...", "password": "...", "password_confirmation": "..." }
    // ─────────────────────────────────────────────
    public function resetPassword(Request $request)
    {
        // ✅ Handle GET request (from email link)
        if ($request->isMethod('get')) {
            return $this->validateResetToken($request);
        }

        // ✅ Handle POST request (actual password reset)
        return $this->performPasswordReset($request);
    }

    private function validateResetToken(Request $request)
    {
        // ✅ Validate GET parameters
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Find the reset record
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // ✅ Check token expiry (60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json([
                'status' => false,
                'message' => 'Reset token has expired. Please request a new one.',
            ], 400);
        }

        // ✅ Verify token hash
        if (! Hash::check($request->token, $record->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid reset token.',
            ], 400);
        }

        // ✅ Token is valid
        return response()->json([
            'status' => true,
            'message' => 'Reset token is valid. Proceed with password reset.',
            'email' => $request->email,
            'token' => $request->token,
            'action' => 'Send POST request to /api/reset-password with new password',
        ]);
    }

    private function performPasswordReset(Request $request)
    {
        // ✅ Validate POST data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        // ✅ Find the reset record
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // ✅ Check token expiry (60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json([
                'status' => false,
                'message' => 'Reset token has expired. Please request a new one.',
            ], 400);
        }

        // ✅ Verify token hash
        if (! Hash::check($request->token, $record->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid reset token.',
            ], 400);
        }

        // ✅ Find broker or driver only
        $user = User::where('email', $request->email)
            ->whereIn('role', ['broker', 'driver'])
            ->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // ✅ Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // ✅ Delete used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // ✅ Revoke all existing tokens (force re-login)
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password has been reset successfully. Please log in.',
        ]);
    }
}
