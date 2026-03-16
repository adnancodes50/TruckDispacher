<?php

namespace App\Http\View\Composers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Auth::check()) {
            $unreadNotifications = Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $unreadCount = Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->count();

            $view->with('unreadNotifications', $unreadNotifications);
            $view->with('unreadCount', $unreadCount);
        } else {
            $view->with('unreadNotifications', collect());
            $view->with('unreadCount', 0);
        }
    }
}
