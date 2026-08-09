<?php
namespace App\Http\Controllers;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $groups   = $user->groupsAsStudent()->with('subject','teacher')->get();
        $unread   = $user->notifications()->unread()->count();
        $recent   = $user->notifications()->latest()->limit(5)->get();
        return view('student.dashboard', compact('groups','unread','recent'));
    }
}
