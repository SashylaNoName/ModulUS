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
            $order = $group->columns()->whereKey($moduleCol->id)->value('sort_order');
            $group->columns()->where('sort_order', '>=', $order)->increment('sort_order');
            $newOrder = $order;
        } else {
            // модуля нет — вставляем перед «Итогами», либо (если и их нет) в конец
            $total = $group->totalColumn();
            if ($total) {
                $order = $group->columns()->whereKey($total->id)->value('sort_order');
                $group->columns()->where('sort_order', '>=', $order)->increment('sort_order');
                $newOrder = $order;
            } else {
                $newOrder = ($group->columns()->max('sort_order') ?? -1) + 1;
            }
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

    /** Настройка суммирования столбца (в какой модуль суммировать) */
    public function updateSumming(Request $request, Column $column)
    {
        if ($column->group->user_id !== auth()->id()) abort(403);
        $data = $request->validate([
            'sum_into' => ['nullable', 'in:1,2,3'],   // null = не суммировать
        ]);
        $column->update(['sum_into' => $data['sum_into'] ?? null]);
        $column->group->recomputeAll();
        return back()->with('success', 'Настройка суммирования сохранена.');
    }

    /** Удалить столбец */
    public function destroy(Column $column)
    {
        if ($column->group->user_id !== auth()->id()) abort(403);
        $column->delete();
        return back()->with('success', 'Столбец удалён.');
    }
}
