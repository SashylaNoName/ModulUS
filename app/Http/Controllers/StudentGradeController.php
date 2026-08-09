<?php
namespace App\Http\Controllers;

class StudentGradeController extends Controller
{
    /** Сводка оценок по всем предметам студента */
    public function index()
    {
        $user = auth()->user();
        $groups = $user->groupsAsStudent()->with(['subject','columns','grades' => fn($q)=>$q->where('user_id',$user->id)])->get();

        return view('student.grades', compact('groups'));
    }
}
