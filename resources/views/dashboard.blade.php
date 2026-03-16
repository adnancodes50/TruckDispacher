<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>Dashboard Overview</span>
        </div>
    </x-slot>

    <div class="space-y-6">

        <!-- Stat cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-xs text-slate-500">Active Drivers</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900">
                    {{ number_format($stats['active_drivers']) }}
                </div>
                <div class="mt-1 text-xs text-slate-500">Registered on platform</div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-xs text-slate-500">Total Brokers</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900">
                    {{ number_format($stats['total_brokers']) }}
                </div>
                <div class="mt-1 text-xs text-slate-500">Active companies</div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-xs text-slate-500">Total Trips</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900">
                    {{ number_format($stats['total_loads']) }}
                </div>
                <div class="mt-1 text-xs text-slate-500">All time</div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-xs text-slate-500">Total Revenue</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900">
                    ${{ number_format($stats['total_revenue'] / 1000000, 1) }}M
                </div>
                <div class="mt-1 text-xs text-slate-500">Platform earnings</div>
            </div>

        </div>


        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

            <!-- Top performing drivers -->
            <section class="xl:col-span-6 bg-white rounded-2xl border border-slate-200">

                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-slate-900">
                            Top Performing Drivers
                        </h3>
                    </div>
                    <a href="{{ route('drivers.index') }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50">
                        View All
                    </a>
                </div>

                <div class="p-4">
                    <ul class="divide-y divide-slate-100">

                        @forelse($topDrivers as $driver)

                        <li class="py-3 flex items-center justify-between">

                            <div class="flex items-center gap-3">

                                <div class="h-9 w-9 rounded-full bg-slate-100 grid place-items-center text-xs font-semibold text-slate-600">
                                    {{ strtoupper(substr($driver->driver->full_name ?? 'D',0,2)) }}
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ $driver->driver->full_name ?? 'Driver' }}
                                    </div>

                                    <div class="text-xs text-slate-500">
                                        ⭐ {{ number_format($driver->rating,1) }} rating
                                    </div>
                                </div>

                            </div>

                            <div class="text-sm font-semibold text-slate-900">
                                {{ number_format($driver->rating,1) }}
                            </div>

                        </li>

                        @empty
                        <li class="py-3 text-sm text-slate-500">
                            No driver reviews yet
                        </li>
                        @endforelse

                    </ul>
                </div>

            </section>



            <!-- Top brokers -->
            <section class="xl:col-span-6 bg-white rounded-2xl border border-slate-200">

                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        {{-- <span class="h-2 w-2 rounded-full bg-brand-red"></span> --}}
                        <h3 class="text-sm font-semibold text-slate-900">
                            Top Brokers
                        </h3>
                    </div>

                    <a href="{{ route('drivers.index') }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50">
                        View All
                    </a>
                </div>


                <div class="p-4">

                    <ul class="divide-y divide-slate-100">

                        @forelse($topBrokers as $broker)

                        <li class="py-3 flex items-center justify-between">

                            <div>

                                <div class="text-sm font-medium text-slate-900">
                                    {{ $broker->broker->company_name ?? 'Broker' }}
                                </div>

                                <div class="text-xs text-slate-500">
                                    {{ $broker->total_reviews }} reviews
                                </div>

                            </div>

                            <div class="text-sm font-semibold text-brand-red">
                                {{ $broker->total_reviews }}
                            </div>

                        </li>

                        @empty

                        <li class="py-3 text-sm text-slate-500">
                            No broker activity yet
                        </li>

                        @endforelse

                    </ul>

                </div>

            </section>



            <!-- Recent activity -->
            <section class="xl:col-span-12 bg-white rounded-2xl border border-slate-200">

                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        {{-- <span class="h-2 w-2 rounded-full bg-slate-400"></span> --}}
                        <h3 class="text-sm font-semibold text-slate-900">
                            Recent Activity
                        </h3>
                    </div>

                    <button class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50">
                        Filters
                    </button>
                </div>

                <div class="p-4">

                    <div class="overflow-x-auto">

                        <table class="min-w-full text-sm">

                            <thead class="text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="text-left py-3 px-3">Activity</th>
                                    <th class="text-left py-3 px-3">Who</th>
                                    <th class="text-left py-3 px-3">Status</th>
                                    <th class="text-right py-3 px-3">Time</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">

                                <tr>
                                    <td class="py-3 px-3 text-slate-900">
                                        Load assigned
                                    </td>

                                    <td class="py-3 px-3 text-slate-600">
                                        Driver
                                    </td>

                                    <td class="py-3 px-3">
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 px-2 py-1 text-xs font-medium">
                                            Assigned
                                        </span>
                                    </td>

                                    <td class="py-3 px-3 text-right text-slate-500">
                                        Recently
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>

        </div>
    </div>
</x-app-layout>
