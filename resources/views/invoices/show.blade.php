<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>Invoice Template</span>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-6 px-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h1 class="text-2xl font-bold">Invoice {{ $invoice->invoice_number }}</h1>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-medium text-slate-600">Job</p>
                    <p>{{ $invoice->job->title ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-600">Date</p>
                    <p>{{ $invoice->created_at->format('Y-m-d') }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-600">Driver</p>
                    <p>{{ $invoice->driver->full_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-600">Broker</p>
                    <p>{{ $invoice->broker->full_name ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-6 border-t pt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><strong>Amount</strong> <span>${{ number_format($invoice->amount, 2) }}</span></div>
                    <div><strong>Due Date</strong> <span>{{ optional($invoice->due_date)->format('Y-m-d') ?? '-' }}</span></div>
                    <div><strong>Status</strong> <span>{{ ucfirst($invoice->status) }}</span></div>
                    <div><strong>PDF</strong> <span>@if($invoice->pdf_url)<a href="{{ $invoice->pdf_url }}" target="_blank" class="text-indigo-600 underline">Open</a>@else N/A @endif</span></div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-2">
                <a href="{{ route('invoices.index') }}" class="rounded-lg border border-slate-300 px-4 py-2">Back</a>
                @if($invoice->pdf_url)
                    <a href="{{ $invoice->pdf_url }}" target="_blank" class="rounded-lg bg-blue-600 px-4 py-2 text-white">Open PDF</a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
