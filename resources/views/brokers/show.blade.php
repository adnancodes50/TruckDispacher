<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            {{-- <span class="h-2.5 w-2.5 rounded-full bg-brand-red"></span> --}}
            <span class="font-semibold">Broker Details</span>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">

        {{-- PROFILE HEADER --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                <div class="flex items-center gap-5">

                    {{-- IMAGE --}}
                    <div class="h-28 w-28 rounded-2xl overflow-hidden border border-slate-200 bg-slate-100">
                        @if($broker->user_image)
                            <img src="{{ asset('storage/' . $broker->user_image) }}"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="h-full w-full grid place-items-center text-2xl font-semibold text-slate-500">
                                {{ strtoupper(substr($broker->full_name,0,2)) }}
                            </div>
                        @endif
                    </div>

                    {{-- NAME --}}
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">
                            {{ $broker->full_name }}
                        </h1>

                        <p class="text-sm text-slate-500 mt-1">
                            Joined {{ $broker->created_at?->format('F j, Y') }}
                        </p>

                        <span class="inline-flex items-center gap-2 mt-3 rounded-full px-3 py-1 text-xs font-semibold
                        {{ $broker->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                            <span class="h-2 w-2 rounded-full
                            {{ $broker->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>

                            {{ $broker->is_active ? 'Active Broker' : 'Inactive Broker' }}
                        </span>
                    </div>
                </div>

                {{-- ACTION BUTTON --}}
                <div>
                    <a href="{{ route('brokers.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">

                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M15 18l-6-6 6-6"
                                  stroke="currentColor"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>

                        Back to Brokers
                    </a>
                </div>

            </div>
        </div>


        {{-- INFORMATION CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- CONTACT --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">
                    Contact Information
                </h2>

                <div class="space-y-3 text-sm text-slate-600">

                    <div class="flex justify-between">
                        <span class="font-medium">Email</span>
                        <span>{{ $broker->email }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="font-medium">Phone</span>
                        <span>{{ $broker->phone }}</span>
                    </div>

                </div>
            </div>


            {{-- REVIEWS --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">
                    Reviews
                </h2>

                <div class="space-y-3 text-sm text-slate-600">

                    <div class="flex justify-between">
                        <span class="font-medium">Total Reviews</span>
                        <span>{{ $broker->total_reviews ?? 0 }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="font-medium">Average Rating</span>
                        <span>{{ $broker->average_rating ?? '—' }}</span>
                    </div>

                </div>
            </div>


            {{-- ACCOUNT STATUS --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 hover:shadow-md transition">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">
                    Account Status
                </h2>

                <div class="space-y-3 text-sm text-slate-600">

                    <div class="flex justify-between">
                        <span class="font-medium">Status</span>

                        <span class="{{ $broker->is_active ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $broker->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="font-medium">Created</span>
                        <span>{{ $broker->created_at?->format('M d, Y') }}</span>
                    </div>

                </div>
            </div>

        </div>

    </div>

    @include('brokers._edit_modal')

</x-app-layout>
