@php
    $settings = \App\Models\Setting::first();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TruckerConnect') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/shiping_icon1.png') }}">
</head>

<body class="font-sans antialiased bg-slate-50">

    <!-- ======================================================= -->
    <!-- SIDEBAR BACKDROP (mobile only) -->
    <!-- ======================================================= -->
    <div id="sidebarBackdrop" onclick="closeSidebar()" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden"></div>

    <!-- ======================================================= -->
    <!-- SIDEBAR -->
    <!-- ======================================================= -->
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out"
        style="background:#0f4a45;">

        <!-- Logo / Brand -->
        <!-- Logo / Brand -->
        <div class="h-16 px-5 flex items-center gap-3 border-b border-white/10 flex-shrink-0">

            <!-- Avatar -->
            <div class="h-9 w-9 rounded-lg grid place-items-center font-bold text-sm"
                style="background:rgba(255,255,255,0.15);">
                {{ strtoupper(substr($settings->platform_name ?? 'TC', 0, 2)) }}
            </div>

            <!-- Business Info -->
            <div class="leading-tight">
                <div class="text-sm font-semibold text-white">
                    {{ $settings->platform_name ?? 'TruckerConnect' }}
                </div>

                <div class="text-xs" style="color:rgba(255,255,255,0.6);">
                    {{ $settings->platform_email ?? 'Admin Panel' }}
                </div>
            </div>

            <!-- Close button (mobile) -->
            <button onclick="closeSidebar()" class="ml-auto lg:hidden text-white/70 hover:text-white">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>

        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto">
            <a href="{{ route('dashboard') }}"
                style="{{ request()->routeIs('dashboard') ? 'background:#f97316; color:#000; font-weight:600;' : '' }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-white hover:bg-white/10 transition">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor"
                        stroke-width="1.8" />
                    <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor"
                        stroke-width="1.8" />
                    <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor"
                        stroke-width="1.8" />
                    <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor"
                        stroke-width="1.8" />
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('drivers.index') }}"
                style="{{ request()->routeIs('drivers.*') ? 'background:#f97316; color:#000; font-weight:600;' : '' }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-white hover:bg-white/10 transition">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8" />
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" />
                </svg>
                <span>Drivers</span>
            </a>
            <a href="{{ route('brokers.index') }}"
                style="{{ request()->routeIs('brokers.*') ? 'background:#f97316; color:#000; font-weight:600;' : '' }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-white hover:bg-white/10 transition">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" />
                    <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.8" />
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" />
                </svg>
                <span>Brokers</span>
            </a>

            <a href="{{ route('settings.index') }}"
                style="{{ request()->routeIs('settings.*') ? 'background:#f97316; color:#000; font-weight:600;' : '' }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-white hover:bg-white/10 transition">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8" />
                    <path
                        d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>Settings</span>
            </a>

        </nav>

        <!-- User profile -->
        <div class="p-4 border-t border-white/10 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full grid place-items-center text-sm font-semibold text-white flex-shrink-0"
                    style="background:rgba(255,255,255,0.2);">
                    {{ strtoupper(substr(Auth::user()->full_name ?? (Auth::user()->name ?? 'U'), 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-white truncate">
                        {{ Auth::user()->full_name ?? (Auth::user()->name ?? 'User') }}</div>
                    <div class="text-xs truncate" style="color:rgba(255,255,255,0.6);">
                        {{ Auth::user()->email ?? '' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit"
                    class="w-full text-xs rounded-lg px-3 py-2 text-white transition hover:bg-white/20"
                    style="background:rgba(255,255,255,0.1);">
                    Log out
                </button>
            </form>
        </div>
    </aside>

    <!-- ======================================================= -->
    <!-- MAIN CONTENT -->
    <!-- ======================================================= -->
    <div class="lg:ml-64 flex flex-col min-h-screen">

        <!-- TOPBAR -->
        <header class="sticky top-0 z-20 h-16 bg-white border-b border-slate-200 flex items-center gap-4 px-4 lg:px-6">
            <!-- Hamburger (mobile) -->
            <button onclick="openSidebar()" class="lg:hidden text-slate-500 hover:text-slate-700 flex-shrink-0">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" />
                </svg>
            </button>

            <!-- Page Title (Header Slot) -->
            @isset($header)
                <div class="hidden md:block">
                    {{ $header }}
                </div>
            @endisset

            <!-- Search -->
            <div class="flex-1">
                <div class="relative max-w-xl">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M21 21l-4.3-4.3m1.3-5.2a7.2 7.2 0 11-14.4 0 7.2 7.2 0 0114.4 0z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <input type="text" placeholder="Search anything…"
                        class="w-full rounded-xl border-slate-200 pl-9 pr-3 py-2 text-sm focus:ring-1"
                        style="focus:border-color:#0f766e;" />
                </div>
            </div>

            <!-- Right side: icons + profile -->
            <div class="flex items-center gap-2 flex-shrink-0">

                <!-- Notification bell -->
                <div class="relative" id="notificationDropdownWrapper">
                    <button onclick="toggleNotifications()"
                        class="relative h-9 w-9 rounded-xl border border-slate-200 grid place-items-center text-slate-500 hover:bg-slate-50 transition">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        @if ($unreadCount > 0)
                            <span
                                class="absolute top-1.5 right-1.5 h-4 w-4 rounded-full text-[10px] font-bold text-white grid place-items-center"
                                style="background:#f97316;">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <!-- Notification Dropdown -->
                    <div id="notificationDropdown"
                        class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl border border-slate-100 shadow-xl py-0 z-50 overflow-hidden text-left">
                        <div
                            class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-800">Notifications</span>
                            @if ($unreadCount > 0)
                                <span
                                    class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-orange-100 text-orange-600">{{ $unreadCount }}
                                    New</span>
                            @endif
                        </div>
                        <div class="max-h-[350px] overflow-y-auto">
                            @forelse($unreadNotifications as $notification)
                                <div onclick="showNotificationDetail('{{ addslashes($notification->title ?? 'New Notification') }}', '{{ addslashes($notification->message) }}', '{{ $notification->created_at->diffForHumans() }}')"
                                    class="px-4 py-3 border-b border-slate-50 hover:bg-slate-50 transition cursor-pointer">
                                    <div class="flex gap-3 text-left">
                                        <div
                                            class="h-8 w-8 rounded-full bg-blue-50 text-blue-600 grid place-items-center flex-shrink-0">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path
                                                    d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" />
                                            </svg>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p class="text-sm font-medium text-slate-800 leading-snug">
                                                {{ $notification->title ?? 'New Notification' }}</p>
                                            <p class="text-xs text-slate-500 line-clamp-2">
                                                {{ $notification->message }}</p>
                                            <p class="text-[10px] text-slate-400 capitalize">
                                                {{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center">
                                    <div
                                        class="h-12 w-12 rounded-2xl bg-slate-50 text-slate-300 grid place-items-center mx-auto mb-3">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path
                                                d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" />
                                        </svg>
                                    </div>
                                    <p class="text-sm text-slate-500">No new notifications</p>
                                </div>
                            @endforelse
                        </div>
                        @if (count($unreadNotifications) > 0)
                            <div class="p-2 bg-slate-50/50 border-t border-slate-100">
                                <a href="{{ route('notifications.index') }}"
                                    class="block w-full py-2 text-center text-xs font-semibold text-[#0f766e] hover:bg-white rounded-lg transition">
                                    View All Notifications
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Global Settings Gear -->
                <a href="{{ route('settings.index') }}"
                    class="h-9 w-9 rounded-xl border border-slate-200 grid place-items-center text-slate-500 hover:bg-slate-50 transition"
                    title="Global Settings">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8">
                        <circle cx="12" cy="12" r="3" />
                        <path
                            d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>


                <!-- Profile dropdown -->
                <div class="relative" id="profileDropdownWrapper">
                    <button onclick="toggleProfileDropdown()"
                        class="flex items-center gap-2.5 rounded-xl border border-slate-200 px-2.5 py-1.5 hover:bg-slate-50 transition">
                        @if (Auth::user()?->user_image)
                            <img src="{{ asset('storage/' . Auth::user()->user_image) }}" alt="Profile"
                                class="h-7 w-7 rounded-full object-cover flex-shrink-0" />
                        @else
                            <div class="h-7 w-7 rounded-full grid place-items-center text-xs font-semibold text-white flex-shrink-0"
                                style="background:#0f766e;">
                                {{ strtoupper(substr(Auth::user()->full_name ?? (Auth::user()->name ?? 'U'), 0, 1)) }}
                            </div>
                        @endif

                        <div class="hidden sm:block leading-tight text-left">
                            <div class="text-xs font-semibold text-slate-800">
                                {{ Auth::user()->full_name ?? (Auth::user()->name ?? 'User') }}</div>
                            <div class="text-xs text-slate-400">{{ ucfirst(Auth::user()->role ?? 'Admin') }}</div>
                        </div>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            class="text-slate-400 hidden sm:block">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>

                    <!-- Dropdown menu -->
                    <div id="profileDropdown"
                        class="hidden absolute right-0 mt-2 w-52 bg-white rounded-2xl border border-slate-100 shadow-xl py-1 z-50 overflow-hidden text-left">
                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                            <div class="text-sm font-semibold text-slate-800">{{ Auth::user()->full_name ?? 'User' }}
                            </div>
                            <div class="text-xs text-slate-400 truncate">{{ Auth::user()->email ?? '' }}</div>
                        </div>

                        <div class="py-1.5">
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                    class="text-slate-400">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" />
                                    <circle cx="12" cy="7" r="4" stroke="currentColor"
                                        stroke-width="1.8" />
                                </svg>
                                My Profile
                            </a>
                            <a href="{{ route('settings.index') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition md:hidden">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                    class="text-slate-400">
                                    <circle cx="12" cy="12" r="3" stroke="currentColor"
                                        stroke-width="1.8" />
                                    <path
                                        d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"
                                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                Global Settings
                            </a>
                        </div>

                        <div class="border-t border-slate-100 py-1.5">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"
                                            stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    Log out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-1 p-4 lg:p-6">
            {{ $slot }}
        </main>
    </div>

    <!-- Sidebar/Notification logic now handled in custom-ui.js -->

    <!-- Notification Detail Modal -->
    <div id="notificationModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeNotificationModal()">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-[#0f766e]/10 text-[#0f766e] sm:mx-0 sm:h-10 sm:w-10">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <div class="flex justify-between items-start">
                                <h3 class="text-lg leading-6 font-bold text-slate-800" id="notifModalTitle">
                                    Notification Detail</h3>
                                <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider"
                                    id="notifModalTime">Just now</span>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-wrap"
                                    id="notifModalMessage">
                                    Notification message content goes here.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50/50 px-4 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button" onclick="closeNotificationModal()"
                        class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-[#0f766e] text-base font-semibold text-white hover:bg-[#0d5f59] focus:outline-none transition sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- TOAST NOTIFICATION -->
    <!-- ======================================================= -->
    @if (session('success') || session('error') || $errors->any())
        <div id="toast"
            class="fixed top-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-lg text-sm font-medium text-white transition-all duration-300"
            style="background: {{ session('error') || $errors->any() ? '#dc2626' : '#0f766e' }}; min-width: 260px;">
            @if (session('error') || $errors->any())
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="flex-shrink-0">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                    <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                <span>{{ session('error') ?? 'Please fix the validation errors.' }}</span>
            @else
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="flex-shrink-0">
                    <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                <span>{{ session('success') }}</span>
            @endif
            <button onclick="dismissToast()" class="ml-auto text-white/70 hover:text-white"
                style="margin-left: 1rem;">&times;</button>
        </div>
    @endif
</body>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

</html>
