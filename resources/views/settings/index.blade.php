<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            {{-- <span class="h-2.5 w-2.5 rounded-full bg-brand-red"></span> --}}
            <span>Global Settings</span>
        </div>
    </x-slot>

    <div class="max-w-none mx-auto py-6 px-4">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Global Settings</h1>
            <p class="text-sm text-slate-500">Configure platform-wide settings and integrations.</p>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="space-y-8">
            @csrf

            <!-- API Keys & Integrations -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 grid place-items-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-slate-800">API Keys & Integrations</h2>
                            <p class="text-xs text-slate-400">Manage Stripe keys and platform secrets.</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Stripe Secret Key</label>
                            <input type="password" name="stripe_secret_key" value="{{ old('stripe_secret_key', $settings->stripe_secret_key ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('stripe_secret_key') border-red-500 @enderror" placeholder="sk_test_...">
                            @error('stripe_secret_key')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Stripe Publishable Key</label>
                            <input type="text" name="stripe_publishable_key" value="{{ old('stripe_publishable_key', $settings->stripe_publishable_key ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('stripe_publishable_key') border-red-500 @enderror" placeholder="pk_test_...">
                            @error('stripe_publishable_key')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Stripe Webhook Secret</label>
                            <input type="password" name="stripe_webhook_secret" value="{{ old('stripe_webhook_secret', $settings->stripe_webhook_secret ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('stripe_webhook_secret') border-red-500 @enderror" placeholder="whsec_...">
                            @error('stripe_webhook_secret')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Google Maps API Key</label>
                            <input type="text" name="google_maps_api_key" value="{{ old('google_maps_api_key', $settings->google_maps_api_key ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('google_maps_api_key') border-red-500 @enderror" placeholder="AIza...">
                            @error('google_maps_api_key')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Configuration -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 grid place-items-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-slate-800">Email Configuration (SMTP)</h2>
                            <p class="text-xs text-slate-400">Configure outgoing mail server settings.</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Mail Host</label>
                            <input type="text" name="mail_host" value="{{ old('mail_host', $settings->mail_host ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('mail_host') border-red-500 @enderror" placeholder="smtp.mailtrap.io">
                            @error('mail_host')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Mail Port</label>
                            <input type="text" name="mail_port" value="{{ old('mail_port', $settings->mail_port ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('mail_port') border-red-500 @enderror" placeholder="2525">
                            @error('mail_port')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Mail Username</label>
                            <input type="text" name="mail_username" value="{{ old('mail_username', $settings->mail_username ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('mail_username') border-red-500 @enderror">
                            @error('mail_username')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Mail Password</label>
                            <input type="password" name="mail_password" value="{{ old('mail_password', $settings->mail_password ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('mail_password') border-red-500 @enderror">
                            @error('mail_password')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Encryption</label>
                            <select name="mail_encryption" class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e]">
                                <option value="tls" {{ ($settings->mail_encryption ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($settings->mail_encryption ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ ($settings->mail_encryption ?? '') == '' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">From Name</label>
                            <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings->mail_from_name ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('mail_from_name') border-red-500 @enderror" placeholder="TruckerConnect">
                            @error('mail_from_name')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">From Address</label>
                            <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings->mail_from_address ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('mail_from_address') border-red-500 @enderror" placeholder="no-reply@truckerconnect.com">
                            @error('mail_from_address')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Platform Configuration -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 grid place-items-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-slate-800">Platform Configuration</h2>
                            <p class="text-xs text-slate-400">Basic information about the platform.</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Platform Name</label>
                            <input type="text" name="platform_name" value="{{ old('platform_name', $settings->platform_name ?? 'TruckerConnect') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('platform_name') border-red-500 @enderror">
                            @error('platform_name')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Contact Email</label>
                            <input type="email" name="platform_email" value="{{ old('platform_email', $settings->platform_email ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('platform_email') border-red-500 @enderror">
                            @error('platform_email')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Contact Phone</label>
                            <input type="text" name="platform_phone" value="{{ old('platform_phone', $settings->platform_phone ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('platform_phone') border-red-500 @enderror">
                            @error('platform_phone')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Settings -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-orange-50 text-orange-600 grid place-items-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-slate-800">Payment Settings</h2>
                            <p class="text-xs text-slate-400">Fees and payout limits.</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Platform Commission (%)</label>
                            <input type="number" step="0.01" name="platform_commission" value="{{ old('platform_commission', $settings->platform_commission ?? 5.00) }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('platform_commission') border-red-500 @enderror">
                            @error('platform_commission')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Minimum Payout ($)</label>
                            <input type="number" step="0.01" name="min_payout" value="{{ old('min_payout', $settings->min_payout ?? 10.00) }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('min_payout') border-red-500 @enderror">
                            @error('min_payout')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Maximum Payout ($)</label>
                            <input type="number" step="0.01" name="max_payout" value="{{ old('max_payout', $settings->max_payout ?? '') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('max_payout') border-red-500 @enderror">
                            @error('max_payout')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification & App Settings -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Notifications -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden h-fit">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-base font-semibold text-slate-800 text-center">Notification Settings</h2>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">Push Notifications</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="push_notifications" {{ ($settings->push_notifications ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">Email Notifications</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="email_notifications" {{ ($settings->email_notifications ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-700">SMS Notifications</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="sms_notifications" {{ ($settings->sms_notifications ?? false) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- App Version -->
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden h-fit">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-base font-semibold text-slate-800 text-center">App Configuration</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">Android App Version</label>
                            <input type="text" name="android_app_version" value="{{ old('android_app_version', $settings->android_app_version ?? '1.0.0') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('android_app_version') border-red-500 @enderror">
                            @error('android_app_version')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-700">iOS App Version</label>
                            <input type="text" name="ios_app_version" value="{{ old('ios_app_version', $settings->ios_app_version ?? '1.0.0') }}"
                                   class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('ios_app_version') border-red-500 @enderror">
                            @error('ios_app_version')
                                <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms & Policies -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-base font-semibold text-slate-800">Terms & Policies</h2>
                </div>
                <div class="p-6 space-y-6">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-700">Terms of Service</label>
                        <textarea name="terms_of_service" rows="6"
                                  class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('terms_of_service') border-red-500 @enderror">{{ old('terms_of_service', $settings->terms_of_service ?? '') }}</textarea>
                        @error('terms_of_service')
                            <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-700">Privacy Policy</label>
                        <textarea name="privacy_policy" rows="6"
                                  class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('privacy_policy') border-red-500 @enderror">{{ old('privacy_policy', $settings->privacy_policy ?? '') }}</textarea>
                        @error('privacy_policy')
                            <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Maintenance Mode -->
            <div class="bg-white rounded-3xl border border-red-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-red-50 bg-red-50/30">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-red-800">Maintenance Mode</h2>
                            <p class="text-xs text-red-500">Take the platform offline for updates.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="maintenance_mode" {{ ($settings->maintenance_mode ?? false) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                        </label>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-slate-700">Maintenance Message</label>
                        <input type="text" name="maintenance_message" value="{{ old('maintenance_message', $settings->maintenance_message ?? 'We are currently performing scheduled maintenance. Please check back later.') }}"
                               class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-red-500 @error('maintenance_message') ring-1 ring-red-500 border-red-500 @enderror" placeholder="Display message to users...">
                        @error('maintenance_message')
                            <p class="text-[10px] text-red-500 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end pt-4">
                <button type="submit"
                        class="px-8 py-3 bg-[#0f766e] text-white rounded-2xl font-semibold text-sm hover:bg-[#0d5f59] transition-all shadow-lg shadow-emerald-900/10">
                    Save All Settings
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
