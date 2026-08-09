<?php
namespace App\Http\Controllers;

use App\Exports\GradebookExport;
use App\Imports\GradesImport;
use App\Imports\StudentsImport;
use App\Models\Group;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    /** Экспорт журнала группы в .xlsx */
    public function export(Group $group)
    {
        if ($group->user_id !== auth()->id()) abort(403);
        return Excel::download(new GradebookExport($group), 'journal_'.$group->name.'.xlsx');
    }

    /** Импорт студентов из Excel (Колонка A = ФИО, B = email) */
    public function importStudents(\Illuminate\Http\Request $request, Group $group)
    {
        if ($group->user_id !== auth()->id()) abort(403);
        $request->validate(['file' => ['required','file','mimes:xlsx,xls']]);
        Excel::import(new StudentsImport($group), $request->file('file'));
        return back()->with('success', 'Студенты импортированы.');
    }

    /** Импорт баллов из Excel (первая колонка = ФИО, далее по столбцам) */
    public function importGrades(\Illuminate\Http\Request $request, Group $group)
    {
        if ($group->user_id !== auth()->id()) abort(403);
        $request->validate(['file' => ['required','file','mimes:xlsx,xls']]);
        Excel::import(new GradesImport($group), $request->file('file'));
        return back()->with('success', 'Баллы импортированы.');
    }
}
