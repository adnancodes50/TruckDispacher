<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span class="h-2.5 w-2.5 rounded-full bg-brand-teal"></span>
            <span>Notifications Centre</span>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Notifications</h1>
                <p class="mt-1 text-sm text-slate-500">Stay updated with the latest activity and alerts.</p>
            </div>
            
            @if($notifications->whereNull('read_at')->count() > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-[#0f766e] hover:bg-[#0f766e]/5 transition border border-slate-200">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="divide-y divide-slate-100">
                @forelse($notifications as $notification)
                    <div class="p-4 sm:p-6 transition hover:bg-slate-50/50 flex gap-4 {{ $notification->read_at ? 'opacity-70' : '' }}" 
                         id="notification-{{ $notification->id }}">
                        <div class="h-10 w-10 rounded-2xl {{ $notification->read_at ? 'bg-slate-100 text-slate-400' : 'bg-brand-teal/10 text-brand-teal' }} grid place-items-center flex-shrink-0">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <h3 class="text-sm font-bold text-slate-800">{{ $notification->title ?? 'Notification' }}</h3>
                                <span class="text-[10px] sm:text-xs font-medium text-slate-400 uppercase tracking-wider">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed max-w-2xl">
                                {{ $notification->message ?? $notification->body ?? '' }}
                            </p>
                            
                            @if(!$notification->read_at)
                                <div class="pt-2">
                                    <button onclick="markAsRead({{ $notification->id }})" class="text-xs font-semibold text-brand-teal hover:underline flex items-center gap-1">
                                        Mark as read
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="h-16 w-16 rounded-3xl bg-slate-50 text-slate-300 grid place-items-center mx-auto mb-4">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">No notifications yet</h3>
                        <p class="text-sm text-slate-400 mt-1">When you receive notifications, they'll appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>

        @if($notifications->hasPages())
            <div class="pt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
