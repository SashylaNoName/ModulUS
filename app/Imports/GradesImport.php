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
    /** @var array<int, true> id столбцов группы, уже занятых сопоставлением */
    private array $used = [];
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

        // пересчитать модули/итог по настройкам суммирования
        $this->group->recomputeForUser((int) $student->id);
    }

    /* ================= Заголовок ================= */

    /** Заголовки колонок с частями ФИО (регистр/пробелы не важны) */
    private const NAME_HEADERS = ['фамилия','имя','отчество','фио','студент','fio','student','full name'];

    private function parseHeader(array $cells): void
    {
        $groupCols = $this->group->columns()->orderBy('sort_order')->get();

        /* Сегмент столбца группы = сколько модулей стоит левее него:
           0 — до 1 модуля, 1 — между 1 и 2, 2 — между 2 и 3, 3 — после 3-го. */
        $segOf = [];
        $seen = 0;
        foreach ($groupCols as $c) {
            if ($c->type === 'module') { $segOf[$c->id] = $seen; $seen++; }
            else $segOf[$c->id] = $seen;
        }

        // Именные колонки определяем ПО ЗАГОЛОВКУ («Фамилия», «Имя», «Студент»…).
        $this->nameCols = [];
        foreach ($cells as $i => $title) {
            $t = $this->norm((string) $title);
            if ($t !== '' && in_array($t, self::NAME_HEADERS, true)) $this->nameCols[] = (int) $i;
        }
        if ($this->nameCols === [] && isset($cells[0])) $this->nameCols = [0];

        // Якоря модулей в файле: ПЕРВОЕ вхождение каждого заголовка модуля.
        // Дубликат («1 модуль» второй раз) якорем не считается.
        $moduleTitles = $groupCols->where('type', 'module')->map(fn ($c) => $this->norm($c->title))->all();
        $anchorByTitle = [];
        foreach ($cells as $i => $title) {
            $t = $this->norm((string) $title);
            if (in_array($t, $moduleTitles, true) && ! isset($anchorByTitle[$t])) {
                $anchorByTitle[$t] = (int) $i;
            }
        }
        $anchorPos = array_values($anchorByTitle);
        sort($anchorPos);
        $fileSeg = function (int $i) use ($anchorPos): int {
            $s = 0;
            foreach ($anchorPos as $p) { if ($p < $i) $s++; }
            return $s;
        };

        // Столбцы группы по заголовку
        $byTitle = [];
        foreach ($groupCols as $c) $byTitle[$this->norm($c->title)][] = $c;

        // Сопоставление: по заголовку, а для промежуточных — ЕЩЁ и по сегменту.
        // Иначе «к/р 1» из «до 1 модуля» склеивается с чужим «к/р 1» из
        // «до 2 модуля», и значения перезаписывают друг друга.
        $matches = [];
        foreach ($cells as $i => $title) {
            if (in_array((int) $i, $this->nameCols, true)) continue;
            $t = $this->norm((string) $title);
            if ($t === '' || ! isset($byTitle[$t])) continue;

            $wantIntermediate = optional($byTitle[$t][0])->type === 'intermediate';
            $fs = $fileSeg((int) $i);

            $found = null;
            foreach ($byTitle[$t] as $c) {
                if (isset($this->used[$c->id])) continue;
                if ($wantIntermediate && $segOf[$c->id] !== $fs) continue;  // чужой сегмент
                $found = $c;
                break;
            }
            // Модули/экзамен/итоги уникальны: повторный заголовок в файле —
            // это дубликат, его значение просто перезапишет то же поле.
            if (! $found && ! $wantIntermediate) $found = $byTitle[$t][0];

            if ($found) {
                $matches[$i] = $found;
                $this->used[$found->id] = true;
            }
            // не нашлось в своём сегменте — уйдём в создание нового столбца
        }

        if ($matches === []) {
            // ни один заголовок не совпал — позиционное сопоставление
            // (файл в структуре экспорта: имя, дальше по порядку столбцов)
            $ordered = $groupCols->values();
            $j = 0;
            foreach ($cells as $i => $_) {
                if (in_array((int) $i, $this->nameCols, true)) continue;
                if (isset($ordered[$j])) $this->colMap[$i] = $ordered[$j];
                $j++;
            }
            return;
        }

        // Проход 2: незнакомые заголовки — создаём перед ближайшим
        // совпавшим столбцом справа (сохраняя порядок из файла)
        foreach ($cells as $i => $title) {
            if (in_array((int) $i, $this->nameCols, true)) continue;
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
        // Граница журнала: новые столбцы НЕ добавляются после «Итогов».
        // Если ориентир правее итогов (или его нет) — вставляем перед «Итогами»,
        // по умолчанию суммируя в 3-й модуль (настраивается в «Суммировании»).
        $total = $this->group->totalColumn();
        if ($total) {
            $totalOrder = $this->group->columns()->whereKey($total->id)->value('sort_order');
            $targetOrder = $target
                ? $this->group->columns()->whereKey($target->id)->value('sort_order')
                : PHP_INT_MAX;
            if ($target === $total || $targetOrder > $totalOrder) {
                $target = $total;
            }
        }

        if ($target) {
            // берём АКТУАЛЬНЫЙ sort_order из БД: модель в памяти могла
            // устареть после предыдущих вставок (сдвигов)
            $order = $this->group->columns()->whereKey($target->id)->value('sort_order');
            $this->group->columns()->where('sort_order', '>=', $order)->increment('sort_order');
            $pos = $sum = null;
            if ($target->type === 'module' && preg_match('/^(\d)/u', $target->title, $m)) {
                $pos = 'before' . $m[1];
                $sum = (int) $m[1];
            } elseif ($target->type === 'total') {
                // встаёт после 3-го модуля, перед «Итогами»
                $sum = 3;
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
