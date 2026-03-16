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
                <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($stats['active_drivers']) }}
                </div>
                <div class="mt-1 text-xs text-slate-500">Registered on platform</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-xs text-slate-500">Total Brokers</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($stats['total_brokers']) }}
                </div>
                <div class="mt-1 text-xs text-slate-500">Active companies</div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-xs text-slate-500">Total Trips</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($stats['total_loads']) }}</div>
                <div class="mt-1 text-xs text-slate-500">All time</div>
            </div>
            {{-- <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-xs text-slate-500">Total Reviews</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($stats['total_reviews']) }}</div>
                <div class="mt-1 text-xs text-slate-500">Driver ratings</div>
            </div> --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <div class="text-xs text-slate-500">Total Revenue</div>
                <div class="mt-2 text-2xl font-semibold text-slate-900">
                    ${{ number_format($stats['total_revenue'] / 1000000, 1) }}M</div>
                <div class="mt-1 text-xs text-slate-500">Platform earnings</div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <!-- Top performing drivers -->
            <section class="xl:col-span-6 bg-white rounded-2xl border border-slate-200">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        {{-- <span class="h-2 w-2 rounded-full bg-brand-teal"></span> --}}
                        <h3 class="text-sm font-semibold text-slate-900">Top Performing Drivers</h3>
                    </div>
                    <button
                        class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50">View</button>
                </div>
                <div class="p-4">
                    <ul class="divide-y divide-slate-100">
                        <li class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-9 w-9 rounded-full bg-slate-100 grid place-items-center text-xs font-semibold text-slate-600">
                                    JS</div>
                                <div>
                                    <div class="text-sm font-medium text-slate-900">John Smith</div>
                                    <div class="text-xs text-slate-500">Top driver</div>
                                </div>
                            </div>
                            <div class="text-sm font-semibold text-slate-900">$42,300</div>
                        </li>
                        <li class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-9 w-9 rounded-full bg-slate-100 grid place-items-center text-xs font-semibold text-slate-600">
                                    SJ</div>
                                <div>
                                    <div class="text-sm font-medium text-slate-900">Sarah Johnson</div>
                                    <div class="text-xs text-slate-500">High rating</div>
                                </div>
                            </div>
                            <div class="text-sm font-semibold text-slate-900">$28,800</div>
                        </li>
                        <li class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-9 w-9 rounded-full bg-slate-100 grid place-items-center text-xs font-semibold text-slate-600">
                                    MB</div>
                                <div>
                                    <div class="text-sm font-medium text-slate-900">Mike Brown</div>
                                    <div class="text-xs text-slate-500">On time</div>
                                </div>
                            </div>
                            <div class="text-sm font-semibold text-slate-900">$22,400</div>
                        </li>
                        <li class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-9 w-9 rounded-full bg-slate-100 grid place-items-center text-xs font-semibold text-slate-600">
                                    ED</div>
                                <div>
                                    <div class="text-sm font-medium text-slate-900">Emily Davis</div>
                                    <div class="text-xs text-slate-500">Good records</div>
                                </div>
                            </div>
                            <div class="text-sm font-semibold text-slate-900">$15,000</div>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Top brokers -->
            <section class="xl:col-span-6 bg-white rounded-2xl border border-slate-200">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        {{-- <span class="h-2 w-2 rounded-full bg-brand-red"></span> --}}
                        <h3 class="text-sm font-semibold text-slate-900">Top Brokers</h3>
                    </div>
                    <button
                        class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50">View</button>
                </div>
                <div class="p-4">
                    <ul class="divide-y divide-slate-100">
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-slate-900">Logistic Inc.</div>
                                <div class="text-xs text-slate-500">734 shipments</div>
                            </div>
                            <div class="text-sm font-semibold text-brand-red">$118,000</div>
                        </li>
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-slate-900">FreightMaster Co.</div>
                                <div class="text-xs text-slate-500">589 shipments</div>
                            </div>
                            <div class="text-sm font-semibold text-brand-red">$94,520</div>
                        </li>
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-slate-900">TransWorld LLC</div>
                                <div class="text-xs text-slate-500">463 shipments</div>
                            </div>
                            <div class="text-sm font-semibold text-brand-red">$81,300</div>
                        </li>
                        <li class="py-3 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-slate-900">Swift Cargo</div>
                                <div class="text-xs text-slate-500">317 shipments</div>
                            </div>
                            <div class="text-sm font-semibold text-brand-red">$70,980</div>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Recent activity -->
            <section class="xl:col-span-12 bg-white rounded-2xl border border-slate-200">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                        <h3 class="text-sm font-semibold text-slate-900">Recent Activity</h3>
                    </div>
                    <button
                        class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50">Filters</button>
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
                                    <td class="py-3 px-3 text-slate-900">Load #1042 assigned</td>
                                    <td class="py-3 px-3 text-slate-600">John Smith</td>
                                    <td class="py-3 px-3">
                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 px-2 py-1 text-xs font-medium">Assigned</span>
                                    </td>
                                    <td class="py-3 px-3 text-right text-slate-500">10m ago</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 text-slate-900">Broker invoice paid</td>
                                    <td class="py-3 px-3 text-slate-600">Logistic Inc.</td>
                                    <td class="py-3 px-3">
                                        <span
                                            class="inline-flex items-center rounded-full bg-slate-50 text-slate-700 ring-1 ring-slate-200 px-2 py-1 text-xs font-medium">Completed</span>
                                    </td>
                                    <td class="py-3 px-3 text-right text-slate-500">1h ago</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-3 text-slate-900">Driver document review</td>
                                    <td class="py-3 px-3 text-slate-600">Sarah Johnson</td>
                                    <td class="py-3 px-3">
                                        <span
                                            class="inline-flex items-center rounded-full bg-red-50 text-red-700 ring-1 ring-red-100 px-2 py-1 text-xs font-medium">Pending</span>
                                    </td>
                                    <td class="py-3 px-3 text-right text-slate-500">3h ago</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
