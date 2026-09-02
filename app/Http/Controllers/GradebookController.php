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

        // диалоги для чата: grade_id => сообщения
        $chat = $grades->filter(fn($g) => $g->comments->isNotEmpty())->mapWithKeys(fn($g) => [
            $g->id => $g->comments->map(fn($c) => [
                'mine'  => $c->user_id === auth()->id(),
                'name'  => $c->author?->name,
                'text'  => $c->text,
                'image' => $c->image ? asset('storage/'.$c->image) : null,
                'file'  => $c->file ? asset('storage/'.$c->file) : null,
                'fname' => $c->file ? basename($c->file) : null,
                'time'  => $c->created_at?->diffForHumans(),
            ])->all(),
        ])->all();

        return view('teacher.gradebook.show', compact('group','grades','chat'));
    }
}
