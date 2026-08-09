<?php
namespace App\Http\Controllers;

use App\Models\Group;

class StudentSubjectController extends Controller
{
    /** Список предметов студента */
    public function index()
    {
        $groups = auth()->user()->groupsAsStudent()->with('subject','teacher')->get();
        return view('student.subjects', compact('groups'));
    }

    /** Страница предмета: горизонтальная таблица баллов + диалоги */
    public function show(Group $group)
    {
        $user = auth()->user();
        if (! $group->students->contains($user->id)) abort(403);

        // студент видит только нескрытые столбцы
        $columns = $group->columns()->where('hidden', false)->orderBy('sort_order')->get();
        $grades  = $group->grades()->where('user_id', $user->id)->with('comments.author')->get()->keyBy('column_id');

        return view('student.subject', compact('group','columns','grades'));
    }
}
