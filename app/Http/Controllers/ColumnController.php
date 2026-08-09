<?php
namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Group;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    /** Создать столбец (вставляется в выбранную позицию — до 1/2/3 модуля) */
    public function store(Request $request, Group $group)
    {
        if ($group->user_id !== auth()->id()) abort(403);
        $data = $request->validate([
            'title'    => ['required','string','max:60'],
            'position' => ['nullable', 'in:before1,before2,before3'],
        ]);
        $position = $data['position'] ?? 'before1';

        // найдём столбец-модуль, перед которым вставляем
        $moduleTitle = match($position) { 'before1' => '1 модуль', 'before2' => '2 модуль', default => '3 модуль' };
        $moduleCol = $group->columns()->where('title', $moduleTitle)->where('type','module')->first();

        if ($moduleCol) {
            // сдвинуть все столбцы с sort_order >= позиции модуля вверх
            $group->columns()->where('sort_order', '>=', $moduleCol->sort_order)->increment('sort_order');
            $newOrder = $moduleCol->sort_order;
        } else {
            $newOrder = ($group->columns()->max('sort_order') ?? -1) + 1;
        }

        $group->columns()->create([
            'title'     => $data['title'],
            'type'      => 'intermediate',
            'position'  => $position,
            'sum_into'  => (int) str_replace('before','',$position),
            'sort_order'=> $newOrder,
        ]);
        return back()->with('success', 'Столбец добавлен.');
    }

    /** Переключить видимость столбца */
    public function toggleVisibility(Column $column)
    {
        if ($column->group->user_id !== auth()->id()) abort(403);
        $column->update(['hidden' => ! $column->hidden]);
        return back()->with('success', 'Видимость столбца изменена.');
    }

    /** Удалить столбец */
    public function destroy(Column $column)
    {
        if ($column->group->user_id !== auth()->id()) abort(403);
        $column->delete();
        return back()->with('success', 'Столбец удалён.');
    }
}
