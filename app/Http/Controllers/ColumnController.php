<?php
namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Group;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    /** Создать столбец */
    public function store(Request $request, Group $group)
    {
        if ($group->user_id !== auth()->id()) abort(403);
        $data = $request->validate([
            'title'    => ['required','string','max:60'],
            'position' => ['nullable', 'in:before1,before2,before3'],
            'type'     => ['nullable', 'in:number,text'],
            'sum'      => ['boolean'],
        ]);
        $group->columns()->create([
            'title'     => $data['title'],
            'type'      => 'intermediate',
            'position'  => $data['position'] ?? 'before1',
            'sum_into'  => !empty($data['sum']) ? (int) str_replace('before','',$data['position'] ?? 'before1') : null,
            'sort_order'=> $group->columns()->max('sort_order') + 1,
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
