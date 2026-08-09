<?php
namespace App\Http\Controllers;

use App\Models\Group;

class GradebookController extends Controller
{
    /** Журнал оценок группы (для преподавателя) */
    public function show(Group $group)
    {
        if ($group->user_id !== auth()->id()) abort(403);

        $group->load(['students','columns','subject']);
        $grades = $group->grades()->with('comments.author')->get()->keyBy(fn($g) => $g->user_id.'_'.$g->column_id);

        return view('teacher.gradebook.show', compact('group','grades'));
    }
}
