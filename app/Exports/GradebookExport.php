<?php
namespace App\Exports;

use App\Models\Group;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradebookExport implements FromArray, WithHeadings, WithStyles
{
    public function __construct(private Group $group) {}

    public function headings(): array
    {
        $cols = $this->group->columns()->orderBy('sort_order')->pluck('title')->all();
        return array_merge(['Студент'], $cols);
    }

    public function array(): array
    {
        $rows = [];
        $columns = $this->group->columns()->orderBy('sort_order')->get();
        foreach ($this->group->students as $student) {
            $row = [$student->name];
            foreach ($columns as $col) {
                $row[] = $this->group->grades()
                    ->where('user_id', $student->id)->where('column_id', $col->id)
                    ->value('value') ?? '';
            }
            $rows[] = $row;
        }
        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
