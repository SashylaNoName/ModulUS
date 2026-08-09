<?php
namespace App\Imports;

use App\Models\Grade;
use App\Models\Group;
use App\Models\User;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithStartRow;

class GradesImport implements OnEachRow, WithStartRow
{
    public function __construct(private Group $group) {}

    public function startRow(): int { return 2; }

    public function onRow(Row $row)
    {
        $row = $row->toArray();
        $studentName = trim($row[0] ?? '');
        $student = User::where('name', $studentName)->first();
        if (! $student) return;

        $columns = $this->group->columns()->orderBy('sort_order')->get();
        foreach ($columns as $i => $col) {
            $value = trim($row[$i + 1] ?? '');
            if ($value === '') continue;
            Grade::updateOrCreate(
                ['group_id' => $this->group->id, 'user_id' => $student->id, 'column_id' => $col->id],
                ['value' => $value]
            );
        }
    }
}
