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
        $import = new StudentsImport($group);
        Excel::import($import, $request->file('file'));

        $parts = [];
        if ($import->created)  $parts[] = 'новых студентов: '.$import->created;
        if ($import->existing) $parts[] = 'уже состояли в группе: '.$import->existing;
        $msg = 'Импорт студентов завершён'
            . ($parts ? ' — '.implode(', ', $parts) : '')
            . ($import->skipped ? '. Пропущено строк без ФИО: '.$import->skipped : '') . '.';
        return back()->with('success', $msg);
    }

    /** Импорт баллов из Excel */
    public function importGrades(\Illuminate\Http\Request $request, Group $group)
    {
        if ($group->user_id !== auth()->id()) abort(403);
        $request->validate(['file' => ['required','file','mimes:xlsx,xls']]);
        $import = new GradesImport($group);
        Excel::import($import, $request->file('file'));

        // подробный отчёт: что импортировано, что создано, что пропущено
        $parts = ['оценок записано: '.$import->imported];
        if ($import->createdColumns) {
            $parts[] = 'создано столбцов: '.$import->createdColumns
                .' («'.implode('», «', $import->createdColumnTitles).'»)';
        }
        if ($import->attachedNames)     $parts[] = 'добавлены в группу: '.implode(', ', $import->attachedNames);
        if ($import->createdStudentNames) $parts[] = 'созданы студенты: '.implode(', ', $import->createdStudentNames);
        if ($import->skippedNames)      $parts[] = 'ПРОПУЩЕНЫ (не найдены): '.implode(', ', $import->skippedNames);

        return back()->with('success', 'Импорт завершён — '.implode('; ', $parts).'.');
    }
}
