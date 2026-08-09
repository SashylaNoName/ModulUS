<?php
namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Group;
use App\Models\Notification;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /** Сохранить/обновить оценку (AJAX) */
    public function update(Request $request, Group $group)
    {
        if ($group->user_id !== auth()->id()) abort(403);
        $data = $request->validate([
            'user_id'   => ['required','exists:users,id'],
            'column_id' => ['required','exists:columns,id'],
            'value'     => ['nullable','string','max:50'],
        ]);
        $grade = Grade::updateOrCreate(
            ['group_id' => $group->id, 'user_id' => $data['user_id'], 'column_id' => $data['column_id']],
            ['value'    => $data['value'] ?? '']
        );

        // уведомление студенту о новом балле
        $column = $grade->column;
        Notification::create([
            'user_id' => $data['user_id'],
            'type'    => 'grade',
            'icon'    => '📊',
            'title'   => 'Поставлена оценка',
            'text'    => 'Получен балл «'.($data['value'] ?: '—').'» за «'.$column->title.'» ('.$group->subject->name.').',
        ]);

        return response()->json(['ok' => true, 'value' => $grade->value]);
    }
}
