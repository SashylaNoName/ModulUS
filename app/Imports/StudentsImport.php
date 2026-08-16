<?php
namespace App\Imports;

use App\Models\Group;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class StudentsImport implements ToModel, WithStartRow
{
    public function __construct(private Group $group) {}

    public function startRow(): int { return 2; } // пропустить заголовок

    public function model(array $row)
    {
        $name  = trim($row[0] ?? '');
        $email = trim($row[1] ?? '');
        if ($name === '') return null;

        $student = User::firstOrCreate(
            ['email' => $email !== '' ? $email : ('student_' . uniqid() . '@stud.local')],
            ['name' => $name, 'password' => 'password', 'role' => 'student']
        );
        $this->group->students()->syncWithoutDetaching([$student->id]);
        return $student;
    }
}
