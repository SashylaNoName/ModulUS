<?php
namespace App\Http\Controllers;

use App\Models\Group;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $subjects = $user->subjects;
        $groups   = $user->groups()->with('subject')->latest()->get();
        $studentsCount = \App\Models\User::whereHas('groupsAsStudent', fn($q)=>$q->whereIn('groups.id', $groups->pluck('id')))->count();
        $unreadCount   = $user->notifications()->unread()->count();

        return view('teacher.dashboard', compact('subjects','groups','studentsCount','unreadCount'));
    }
}
