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
            'link'    => route('student.subject.show', $group, false),
        ]);

        // пересчёт модулей/итога по настройкам суммирования
        $group->recomputeForUser((int) $data['user_id']);

        // актуальные значения модулей (и итога) — чтобы фронт сразу
        // обновил ячейки, без перезагрузки страницы
        $cells = [];
        foreach ($group->moduleColumns() as $mc) {
            $cells[$mc->id] = (string) ($group->grades()
                ->where('user_id', $data['user_id'])->where('column_id', $mc->id)->value('value') ?? '');
        }
        if ($group->sum_total && $total = $group->totalColumn()) {
            $cells[$total->id] = (string) ($group->grades()
                ->where('user_id', $data['user_id'])->where('column_id', $total->id)->value('value') ?? '');
        }

        return response()->json(['ok' => true, 'value' => $grade->value, 'cells' => $cells]);
    }
}
