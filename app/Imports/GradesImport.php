<?php
namespace App\Imports;

use App\Models\Column;
use App\Models\Grade;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

/**
 * Импорт баллов из Excel.
 *
 * Ожидаемая структура файла:
 *   первая строка  — заголовки;
 *   первые колонки — ФИО студента (одна «ФИО»/«Студент» ИЛИ несколько:
 *                    «Фамилия», «Имя», … — значения склеиваются пробелом);
 *   остальные      — заголовки столбцов журнала.
 *
 * Сопоставление столбцов — по заголовку без учёта регистра, пробелов
 * и «*» («Итоги» = «Итоги*»). Незнакомые заголовки (напр. «к/р 1»)
 * создаются как промежуточные столбцы на своей позиции из файла.
 * Студенты из файла, которых нет в группе, добавляются в неё
 * (ищутся глобально, иначе создаются).
 */
class GradesImport implements OnEachRow, WithStartRow
{
    /* Статистика для отчёта пользователю */
    public int $imported = 0;
    public array $skippedNames = [];      // строки без студента
    public int $createdStudents = 0;
    public array $createdStudentNames = [];
    public array $attachedNames = [];     // существующие, добавленные в группу
    public int $createdColumns = 0;
    public array $createdColumnTitles = [];

    /** @var array<int, Column> индекс колонки файла → столбец группы */
    private array $colMap = [];
    /** @var int[] индексы колонок с частями ФИО */
    private array $nameCols = [];
    private bool $headerDone = false;

    public function __construct(private Group $group) {}

    public function startRow(): int { return 1; } // первая строка — заголовки

    public function onRow(Row $row)
    {
        $cells = $row->toArray();

        if (! $this->headerDone) {
            $this->headerDone = true;
            $this->parseHeader($cells);
            return;
        }

        // ФИО = склейка значений «именных» колонок
        $parts = [];
        foreach ($this->nameCols as $i) {
            $v = trim((string) ($cells[$i] ?? ''));
            if ($v !== '') $parts[] = $v;
        }
        $name = trim(implode(' ', $parts));
        if ($name === '') { $this->skippedNames[] = '(пустая строка)'; return; }

        $student = $this->resolveStudent($name);
        if (! $student) { $this->skippedNames[] = $name; return; }

        foreach ($this->colMap as $i => $col) {
            $value = trim((string) ($cells[$i] ?? ''));
            if ($value === '') continue;
            Grade::updateOrCreate(
                ['group_id' => $this->group->id, 'user_id' => $student->id, 'column_id' => $col->id],
                ['value' => $value]
            );
            $this->imported++;
        }
    }

    /* ================= Заголовок ================= */

    private function parseHeader(array $cells): void
    {
        $groupCols = $this->group->columns()->orderBy('sort_order')->get();
        $byTitle = [];
        foreach ($groupCols as $c) $byTitle[$this->norm($c->title)] = $c;

        // проход 1: какие колонки файла совпали со столбцами группы
        $matches = [];
        foreach ($cells as $i => $title) {
            if ($i == 0) continue;                       // 0-я колонка — всегда имя
            $t = $this->norm((string) $title);
            if ($t !== '' && isset($byTitle[$t])) $matches[$i] = $byTitle[$t];
        }

        if ($matches === []) {
            // ни один заголовок не совпал — позиционное сопоставление
            // (файл в структуре экспорта: A=ФИО, дальше по порядку столбцов)
            $this->nameCols = [0];
            $ordered = $groupCols->values();
            foreach ($cells as $i => $_) {
                if ($i == 0) continue;
                if (isset($ordered[$i - 1])) $this->colMap[$i] = $ordered[$i - 1];
            }
            return;
        }

        $firstMatched = min(array_keys($matches));
        $this->nameCols = range(0, $firstMatched - 1);

        // проход 2: совпавшие берём, незнакомые — создаём перед ближайшим
        // совпавшим столбцом справа (сохраняя порядок из файла)
        foreach ($cells as $i => $title) {
            if ($i < $firstMatched) continue;
            $t = $this->norm((string) $title);
            if ($t === '') continue;
            if (isset($matches[$i])) { $this->colMap[$i] = $matches[$i]; continue; }

            $target = null;
            foreach ($matches as $j => $mc) { if ($j > $i) { $target = $mc; break; } }
            $col = $this->createColumn(trim((string) $title), $target);
            $this->colMap[$i] = $col;
            $matches[$i] = $col;   // чтобы следующие новые вставлялись после него
        }
    }

    private function norm(string $s): string
    {
        $s = preg_replace('/\s+/u', ' ', trim($s));
        $s = str_replace('*', '', $s);
        return mb_strtolower($s);
    }

    private function createColumn(string $title, ?Column $target): Column
    {
        if ($target) {
            // берём АКТУАЛЬНЫЙ sort_order из БД: модель в памяти могла
            // устареть после предыдущих вставок (сдвигов)
            $order = $this->group->columns()->whereKey($target->id)->value('sort_order');
            $this->group->columns()->where('sort_order', '>=', $order)->increment('sort_order');
            $pos = $sum = null;
            if ($target->type === 'module' && preg_match('/^(\d)/u', $target->title, $m)) {
                $pos = 'before' . $m[1];
                $sum = (int) $m[1];
            }
        } else {
            $order = ($this->group->columns()->max('sort_order') ?? -1) + 1;
            $pos = $sum = null;
        }

        $col = $this->group->columns()->create([
            'title' => $title, 'type' => 'intermediate',
            'position' => $pos, 'sum_into' => $sum, 'sort_order' => $order,
        ]);
        $this->createdColumns++;
        $this->createdColumnTitles[] = $title;
        return $col;
    }

    /* ================= Студент ================= */

    private function resolveStudent(string $name): ?User
    {
        $members = $this->group->students()->get();

        // 1) точное совпадение с участником группы
        if ($m = $members->firstWhere('name', $name)) return $m;

        // 2) участник, чьё ФИО начинается с имени из файла (в файле нет отчества)
        $ln = mb_strtolower($name);
        foreach ($members as $m) {
            if (mb_stripos(mb_strtolower($m->name), $ln) === 0) return $m;
        }

        // 3) существующий студент (глобально) — добавляем в группу
        $u = User::where('role', 'student')->where('name', $name)->first()
            ?: User::where('role', 'student')
                ->whereRaw('lower(name) like ?', [$ln . '%'])->first();
        if ($u) {
            $this->group->students()->syncWithoutDetaching([$u->id]);
            $this->attachedNames[] = $u->name;
            return $u;
        }

        // 4) создаём нового студента и добавляем в группу
        $slug = Str::slug($name, '_');
        $u = User::create([
            'name'     => $name,
            'email'    => ($slug !== '' ? $slug : 'student') . '_' . uniqid() . '@stud.local',
            'password' => 'password',
            'role'     => 'student',
        ]);
        $this->group->students()->attach($u->id);
        $this->createdStudents++;
        $this->createdStudentNames[] = $name;
        return $u;
    }
}
