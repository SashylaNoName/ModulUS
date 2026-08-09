<?php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Grade;
use App\Models\Notification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /** Отправить сообщение в диалог по оценке (с возможными фото/файлом) */
    public function store(Request $request, Grade $grade)
    {
        $user = auth()->user();
        // преподаватель-владелец группы ИЛИ студент-владелец оценки
        if (! ($grade->group->user_id === $user->id || $grade->user_id === $user->id)) abort(403);

        $data = $request->validate([
            'text'  => ['nullable','string','max:2000'],
            'image' => ['nullable','image','max:5120'],   // фото, до 5МБ
            'file'  => ['nullable','file','max:10240'],   // файл, до 10МБ
        ]);

        $imagePath = $request->hasFile('image') ? $request->file('image')->store('chat','public') : null;
        $filePath  = $request->hasFile('file')  ? $request->file('file')->store('chat','public')  : null;

        $comment = Comment::create([
            'grade_id' => $grade->id,
            'user_id'  => $user->id,
            'text'     => $data['text'] ?? null,
            'image'    => $imagePath,
            'file'     => $filePath,
        ]);

        // уведомление второй стороне + ссылка на обсуждение
        $recipientId = $user->isTeacher() ? $grade->user_id : $grade->group->user_id;
        // ссылка: студенту — на предмет, преподавателю — на журнал
        $link = $user->isTeacher()
            ? route('student.subject.show', $grade->group, false)
            : route('teacher.gradebook.show', $grade->group, false);
        Notification::create([
            'user_id' => $recipientId,
            'type'    => 'comment',
            'icon'    => '💬',
            'title'   => $user->isTeacher() ? 'Новый комментарий преподавателя' : 'Ответ студента',
            'text'    => 'По оценке «'.$grade->column->title.'» ('.$grade->group->subject->name.').',
            'link'    => $link,
        ]);

        return back()->with('success', 'Сообщение отправлено.');
    }
}
