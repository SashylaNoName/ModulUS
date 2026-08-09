<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Список уведомлений текущего пользователя */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = $user->notifications()->latest();
        if ($request->filter === 'unread') $query->unread();
        $items = $query->get();
        return view('notifications', compact('items'));
    }

    /** Отметить все прочитанными */
    public function markAllRead()
    {
        auth()->user()->notifications()->unread()->update(['is_read' => true]);
        return back()->with('success', 'Все уведомления отмечены прочитанными.');
    }

    /** AJAX: счётчик непрочитанных (для polling в шапке) */
    public function unreadCount()
    {
        return response()->json(['count' => auth()->user()->notifications()->unread()->count()]);
    }
}
