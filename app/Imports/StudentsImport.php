<?php
namespace App\Imports;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class StudentsImport implements ToModel, WithStartRow
{
    public int $created = 0;    // создано новых пользователей
    public int $existing = 0;   // уже состояли в группе
    public int $skipped = 0;    // строк без ФИО

    public function __construct(private Group $group) {}

    public function startRow(): int { return 2; } // пропустить заголовок

    public function model(array $row)
    {
        $name  = trim((string) ($row[0] ?? ''));
        $email = trim((string) ($row[1] ?? ''));
        if ($name === '') { $this->skipped++; return null; }

        $wasMember = $this->group->students()->where('users.id',
            User::where('email', $email)->value('id')
        )->exists();

        $student = User::firstOrCreate(
            ['email' => $email !== '' ? $email : ((Str::slug($name, '_') ?: 'student') . '_' . uniqid() . '@stud.local')],
            ['name' => $name, 'password' => 'password', 'role' => 'student']
        );
        $this->group->students()->syncWithoutDetaching([$student->id]);

        $wasMember ? $this->existing++ : $this->created++;
        return $student;
    }
}
