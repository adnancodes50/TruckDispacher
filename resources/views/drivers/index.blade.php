<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>Driver Management</span>
        </div>
    </x-slot>

    <div class="space-y-4 lg:space-y-6">

        <!-- Page heading + Search + Add button -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl lg:text-2xl font-semibold text-slate-900">Driver Management</h1>
            </div>
            <div class="flex flex-1 sm:flex-none items-center gap-2 justify-end">
                {{-- <form method="GET" action="{{ route('drivers.index') }}" class="w-full sm:w-auto">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search drivers..."
                           class="w-full sm:w-64 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-1 focus:border-[#0f766e]" />
                </form> --}}
                <button onclick="openCreateModal()"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-medium hover:opacity-90 transition"
                   style="background:#0f766e;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
                    Add New Driver
                </button>
            </div>
        </div>


        <!--     Search -->
        {{-- <div class="bg-white rounded-2xl border border-slate-200 p-3 lg:p-4">
            <form method="GET" action="{{ route('drivers.index') }}" class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21 21l-4.3-4.3m1.3-5.2a7.2 7.2 0 11-14.4 0 7.2 7.2 0 0114.4 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search drivers by name, email, or phone..."
                       class="w-full rounded-xl border-slate-200 pl-9 pr-4 py-2.5 text-sm" />
            </form>
        </div> --}}

        <!-- ============================================================ -->
        <!-- RESPONSIVE TABLE (all screens) -->
        <!-- ============================================================ -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="driversTable" class="min-w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="text-left py-3 px-5 text-xs font-semibold text-slate-500 uppercase">Driver Name</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Contact</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">License</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Truck</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase">Status</th>
                            <th class="text-right py-3 px-5 text-xs font-semibold text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-3">
                                        @if($driver->user_image)
                                            <img src="{{ asset('storage/' . $driver->user_image) }}" alt="Driver Image" class="h-9 w-9 rounded-full object-cover flex-shrink-0" />
                                        @else
                                            <div class="h-9 w-9 rounded-full grid place-items-center text-xs font-semibold text-white flex-shrink-0" style="background:#0f766e;">
                                                {{ strtoupper(substr($driver->full_name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $driver->full_name }}</div>
                                            <div class="text-xs text-slate-400">Joined {{ $driver->created_at?->format('M Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-700">{{ $driver->email }}</div>
                                    <div class="text-xs text-slate-400">{{ $driver->phone }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-mono text-slate-600">{{ $driver->license_number ?? '—' }}</td>
                                <td class="py-3.5 px-4 text-slate-600 text-xs">{{ $driver->truck_info ?? '—' }}</td>
                                <td class="py-3.5 px-4">
                                    <form action="{{ route('drivers.toggle-status', $driver) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 transition {{ $driver->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-red-50 text-red-700 ring-red-100' }}">
                                            {{ $driver->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('drivers.show', $driver) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600 hover:bg-slate-50 transition">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 4a6 6 0 110 12 6 6 0 010-12zm0 2a4 4 0 100 8 4 4 0 000-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            View
                                        </a>
                                        <button onclick='openEditModal(@json($driver), "driver")'
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600 hover:bg-slate-50 transition">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('drivers.destroy', $driver) }}" onsubmit="return confirm('Delete this driver?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-red-100 text-xs text-red-600 hover:bg-red-50 transition">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400 text-sm">
                                    No drivers found.
                                    <button onclick="openCreateModal()" class="underline ml-1" style="color:#0f766e;">Add one now</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <style>
            #driversTable_wrapper {
                margin-top: 0.5rem;
            }
            .dt-top {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            @media (min-width: 768px) {
                .dt-top {
                    flex-direction: row;
                    align-items: center;
                }
            }
            .dt-length {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .dt-filter {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                justify-content: flex-end;
            }
            #driversTable_wrapper .dataTables_filter label,
            #driversTable_wrapper .dataTables_length label {
                font-size: 0.82rem;
                color: #475569;
                font-weight: 600;
                margin-right: 0.25rem;
                margin-left: .3rem;

            }
            #driversTable_wrapper .dataTables_filter input,
            #driversTable_wrapper .dataTables_length select {
                border: 1px solid #cbd5e1;
                border-radius: 0.75rem;
                padding: 0.35rem 1.25rem;
                    min-width: 60px;
                height: 2.2rem;
                background: #fff;
                box-shadow: none;
            }
            #driversTable_wrapper .dataTables_filter input {
                width: 210px;
            }
            #driversTable_wrapper .dataTables_info,
            #driversTable_wrapper .dataTables_paginate {
                padding: 0.45rem 0;
                margin-left: 1.3rem;
                margin-bottom: .5rem;
                margin-right: 1.3rem;
            }
            #driversTable_wrapper .dataTables_paginate .paginate_button {
                padding: 0.3rem 0.7rem;
                border-radius: 0.5rem;
                border: 1px solid #cbd5e1;
                margin-left: 0.2rem;
            }
            #driversTable_wrapper .dataTables_paginate .paginate_button.current {
                background: #0f766e;
                color: white !important;
                border-color: #0f766e;
            }
            #driversTable_wrapper .dataTables_length select:focus,
            #driversTable_wrapper .dataTables_filter input:focus,
            #driversTable_wrapper .dataTables_paginate .paginate_button:focus {
                outline: 2px solid #0f766e;
                outline-offset: 2px;
            }
            #driversTable thead th {
                border-bottom: 2px solid #cbd5e1;
                background: #f8fafc;
            }
            #driversTable tbody tr {
                border-bottom: 1px solid #e2e8f0;
            }
            #driversTable tbody tr:last-child {
                border-bottom: none;
            }
            #driversTable.dataTable tbody td,
            #driversTable.dataTable tbody th {
                border-bottom: 1px solid #e2e8f0;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.jQuery && $.fn.dataTable) {
                    $('#driversTable').DataTable({
                        responsive: {
                            details: {
                                display: $.fn.dataTable.Responsive.display.childRow,
                                renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                                    tableClass: 'min-w-full text-sm'
                                })
                            }
                        },
                        autoWidth: false,
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        dom: '<"dt-top flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4"<"dt-length"l><"dt-filter ml-auto"f>><"overflow-x-auto"t><"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-4"ip>',
                        columnDefs: [
                            { orderable: false, targets: [5] }
                        ],
                        language: {
                            search: 'Filter:',
                            lengthMenu: 'Show _MENU_ entries',
                            info: 'Showing _START_ to _END_ of _TOTAL_ drivers',
                            paginate: {
                                next: 'Next',
                                previous: 'Prev'
                            }
                        }
                    });
                }
            });
        </script>

        <!-- ============================================================ -->
        <!-- MOBILE CARDS (currently hidden; table is responsive on all screens) -->
        <!-- ============================================================ -->
        <div class="md:hidden hidden space-y-3">
            @forelse($drivers as $driver)
                <div class="bg-white rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            @if($driver->user_image)
                                <img src="{{ asset('storage/' . $driver->user_image) }}" alt="Driver Image" class="h-10 w-10 rounded-full object-cover flex-shrink-0" />
                            @else
                                <div class="h-10 w-10 rounded-full grid place-items-center text-sm font-semibold text-white flex-shrink-0" style="background:#0f766e;">
                                    {{ strtoupper(substr($driver->full_name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <div class="font-semibold text-slate-900">{{ $driver->full_name }}</div>
                                <div class="text-xs text-slate-400">{{ $driver->email }}</div>
                            </div>
                        </div>
                        <form action="{{ route('drivers.toggle-status', $driver) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 transition {{ $driver->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-red-50 text-red-700 ring-red-100' }}">
                                {{ $driver->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-500">
                        <div><span class="font-medium text-slate-700">Phone:</span> {{ $driver->phone }}</div>
                        <div><span class="font-medium text-slate-700">License:</span> {{ $driver->license_number ?? '—' }}</div>
                        <div class="col-span-2"><span class="font-medium text-slate-700">Truck:</span> {{ $driver->truck_info ?? '—' }}</div>
                    </div>

                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-2">
                        <a href="{{ route('drivers.show', $driver) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 rounded-lg border border-slate-200 text-xs text-slate-600 hover:bg-slate-50 transition">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 4a6 6 0 110 12 6 6 0 010-12z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            View
                        </a>
                        <button onclick='openEditModal(@json($driver), "driver")'
                                class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 rounded-lg border border-slate-200 text-xs text-slate-600 hover:bg-slate-50 transition">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Edit
                        </button>
                        <form method="POST" action="{{ route('drivers.destroy', $driver) }}" class="flex-1" onsubmit="return confirm('Delete this driver?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 py-2 rounded-lg border border-red-100 text-xs text-red-600 hover:bg-red-50 transition">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 py-12 text-center text-slate-400 text-sm">
                    No drivers found.
                    <button onclick="openCreateModal()" class="underline ml-1" style="color:#0f766e;">Add one now</button>
                </div>
            @endforelse

            @if($drivers->hasPages())
                <div class="px-1 py-3">
                    {{ $drivers->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- CREATE MODAL -->
    <!-- ============================================================ -->
    <div id="createModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white rounded-t-2xl">
                <h2 class="text-base font-semibold text-slate-900">Add New Driver</h2>
                <button onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" action="{{ route('drivers.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Full Name *</label>
                        <input id="create_full_name" type="text" name="full_name" value="{{ old('full_name') }}" required
                               class="w-full rounded-xl border-slate-200 text-sm" placeholder="John Smith" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Phone *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                               class="w-full rounded-xl border-slate-200 text-sm" placeholder="555-123-4567" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full rounded-xl border-slate-200 text-sm" placeholder="driver@example.com" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Password *</label>
                        <input type="password" name="password" required class="w-full rounded-xl border-slate-200 text-sm" placeholder="Min 8 characters" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required class="w-full rounded-xl border-slate-200 text-sm" />
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">License Number</label>
                        <input type="text" name="license_number" value="{{ old('license_number') }}" class="w-full rounded-xl border-slate-200 text-sm" placeholder="DL-123456" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Truck Info</label>
                        <input type="text" name="truck_info" value="{{ old('truck_info') }}" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Semi Truck / Plate" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Profile Image</label>
                    <div class="mb-2">
                        <div id="create_initials" class="h-20 w-20 rounded-lg bg-slate-100 grid place-items-center text-xl font-semibold text-slate-500"></div>
                        <img id="create_image_preview" src="" alt="Preview" class="hidden h-20 w-20 rounded-lg object-cover border border-slate-200" />
                    </div>
                    <input id="create_user_image" type="file" name="user_image" accept="image/*" class="w-full rounded-xl border-slate-200 text-sm" />
                </div>
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeCreateModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-white text-sm font-medium hover:opacity-90" style="background:#0f766e;">Create Driver</button>
                </div>
            </form>
        </div>
    </div>

    @include('drivers._edit_modal')

</x-app-layout>
