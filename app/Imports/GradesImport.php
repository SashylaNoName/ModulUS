<?php
namespace App\Imports;

use App\Models\Grade;
use App\Models\Group;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithStartRow;

class GradesImport implements OnEachRow, WithStartRow
{
    /** @var array<int, \App\Models\Column> карта: индекс колонки файла → столбец группы */
    private array $headerMap = [];
    private bool $headerLoaded = false;

    public function __construct(private Group $group) {}

    public function startRow(): int { return 1; } // первая строка — заголовки

    public function onRow(Row $row)
    {
        $row = $row->toArray();

        // Заголовок: сопоставляем столбцы файла со столбцами группы ПО НАЗВАНИЮ
        if (! $this->headerLoaded) {
            $this->headerLoaded = true;
            foreach ($row as $i => $title) {
                $title = trim((string) $title);
                if ($i === 0 || $title === '') continue;
                $col = $this->group->columns()->where('title', $title)->first();
                if ($col) $this->headerMap[$i] = $col;
            }
            return;
        }

        $studentName = trim((string) ($row[0] ?? ''));
        if ($studentName === '') return;

        // ВАЖНО: оценки ставятся только участникам ЭТОЙ группы
        // (раньше студент искался по ФИО глобально — оценки «прилипали»
        //  посторонним студентам с совпадающим именем)
        $student = $this->group->students()->where('name', $studentName)->first();
        if (! $student) return;

        foreach ($this->headerMap as $i => $col) {
            $value = trim((string) ($row[$i] ?? ''));
            if ($value === '') continue;
            Grade::updateOrCreate(
                ['group_id' => $this->group->id, 'user_id' => $student->id, 'column_id' => $col->id],
                ['value' => $value]
            );
        }
    }
}
