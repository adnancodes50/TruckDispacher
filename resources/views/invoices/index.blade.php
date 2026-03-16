<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>Invoice Management</span>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-6 px-4">
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Invoice Management</h1>
                <p class="text-sm text-slate-500">Create or update the single invoice used in admin view.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
            <form action="{{ route('invoices.storeOrUpdate') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700">Invoice Number</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number ?? '') }}" required
                           class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('invoice_number') border-red-500 @enderror" />
                    @error('invoice_number') <p class="text-[10px] text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700">Amount</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $invoice->amount ?? '') }}" required
                           class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('amount') border-red-500 @enderror" />
                    @error('amount') <p class="text-[10px] text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', optional($invoice)->due_date?->format('Y-m-d') ?? '') }}" required
                           class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('due_date') border-red-500 @enderror" />
                    @error('due_date') <p class="text-[10px] text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700">Status</label>
                    <select name="status" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('status') border-red-500 @enderror">
                        @php $status = old('status', $invoice->status ?? 'sent'); @endphp
                        <option value="sent" {{ $status === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                    @error('status') <p class="text-[10px] text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">Invoice PDF URL</label>
                    <input type="url" name="pdf_url" value="{{ old('pdf_url', $invoice->pdf_url ?? '') }}"
                           class="w-full rounded-xl border-slate-200 text-sm focus:ring-1 focus:border-[#0f766e] @error('pdf_url') border-red-500 @enderror" placeholder="https://..." />
                    @error('pdf_url') <p class="text-[10px] text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2 text-right">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-white bg-[#0f766e] hover:bg-[#0d5f59]">{{ $invoice ? 'Update Invoice' : 'Create Invoice' }}</button>
                </div>
            </form>
        </div>

        @if($invoice)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">Current Invoice</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</div>
                    <div><strong>Status:</strong> {{ ucfirst($invoice->status) }}</div>
                    <div><strong>Amount:</strong> ${{ number_format($invoice->amount, 2) }}</div>
                    <div><strong>Due Date:</strong> {{ optional($invoice->due_date)->format('Y-m-d') }}</div>
                    <div><strong>Job:</strong> {{ optional($invoice->job)->title ?? '-' }}</div>
                    <div><strong>Driver:</strong> {{ optional($invoice->driver)->full_name ?? '-' }}</div>
                    <div><strong>Broker:</strong> {{ optional($invoice->broker)->full_name ?? '-' }}</div>
                    <div><strong>PDF URL:</strong> @if($invoice->pdf_url) <a href="{{ $invoice->pdf_url }}" target="_blank" class="text-indigo-600 underline">View</a> @else - @endif</div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">Completed Jobs Without Invoice</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th class="border p-2">Job</th>
                                <th class="border p-2">Driver</th>
                                <th class="border p-2">Broker</th>
                                <th class="border p-2">Amount</th>
                                <th class="border p-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingJobs as $job)
                                <tr>
                                    <td class="border p-2">{{ $job->title }}</td>
                                    <td class="border p-2">{{ $job->driver->full_name ?? '-' }}</td>
                                    <td class="border p-2">{{ $job->broker->full_name ?? '-' }}</td>
                                    <td class="border p-2">${{ number_format($job->payment_rate ?? 0, 2) }}</td>
                                    <td class="border p-2">
                                        <form method="POST" action="{{ route('invoices.generateFromJob', $job) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1 text-white">Create Invoice</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Invoice List</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-700">
                    <thead>
                        <tr>
                            <th class="border p-2">#</th>
                            <th class="border p-2">Invoice</th>
                            <th class="border p-2">Job</th>
                            <th class="border p-2">Driver</th>
                            <th class="border p-2">Broker</th>
                            <th class="border p-2">Amount</th>
                            <th class="border p-2">Due</th>
                            <th class="border p-2">Status</th>
                            <th class="border p-2">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                            <tr class="hover:bg-slate-50">
                                <td class="border p-2">{{ $inv->id }}</td>
                                <td class="border p-2">{{ $inv->invoice_number }}</td>
                                <td class="border p-2">{{ $inv->job->title ?? '-' }}</td>
                                <td class="border p-2">{{ $inv->driver->full_name ?? '-' }}</td>
                                <td class="border p-2">{{ $inv->broker->full_name ?? '-' }}</td>
                                <td class="border p-2">${{ number_format($inv->amount, 2) }}</td>
                                <td class="border p-2">{{ optional($inv->due_date)->format('Y-m-d') ?? '-' }}</td>
                                <td class="border p-2">{{ ucfirst($inv->status) }}</td>
                                <td class="border p-2"><a href="{{ route('invoices.show', $inv) }}" class="rounded-lg bg-green-600 px-3 py-1 text-white">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($invoice)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
                <h2 class="text-lg font-bold text-slate-800 mb-4">Current Invoice Template</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</div>
                    <div><strong>Status:</strong> {{ ucfirst($invoice->status) }}</div>
                    <div><strong>Amount:</strong> ${{ number_format($invoice->amount, 2) }}</div>
                    <div><strong>Due Date:</strong> {{ optional($invoice->due_date)->format('Y-m-d') }}</div>
                    <div><strong>Job:</strong> {{ optional($invoice->job)->title ?? '-' }}</div>
                    <div><strong>Driver:</strong> {{ optional($invoice->driver)->full_name ?? '-' }}</div>
                    <div><strong>Broker:</strong> {{ optional($invoice->broker)->full_name ?? '-' }}</div>
                    <div><strong>PDF URL:</strong> @if($invoice->pdf_url) <a href="{{ $invoice->pdf_url }}" target="_blank" class="text-indigo-600 underline">View</a> @else - @endif</div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
