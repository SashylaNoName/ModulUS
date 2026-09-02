<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $fillable = ['name','subject_id','user_id','level','year','number','invite_token',
                           'sum_m1','sum_m2','sum_m3','sum_total'];

    protected $casts = ['sum_m1' => 'boolean', 'sum_m2' => 'boolean',
                        'sum_m3' => 'boolean', 'sum_total' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Group $g) {
            if (empty($g->invite_token)) {
                $g->invite_token = Str::random(16);
            }
        });
    }

    /* Связи */
    public function subject()   { return $this->belongsTo(Subject::class); }
    public function teacher()   { return $this->belongsTo(User::class, 'user_id'); }
    public function students()  { return $this->belongsToMany(User::class)->where('role','student')->orderBy('name'); }
    public function columns()   { return $this->hasMany(Column::class)->orderBy('sort_order'); }
    public function grades()    { return $this->hasMany(Grade::class); }

    /** Кол-во студентов (для списков) */
    public function getStudentsCountAttribute(): int { return $this->students()->count(); }

    /** Ссылка-приглашение */
    public function getInviteUrlAttribute(): string { return url('/join/' . $this->invite_token); }

    /** Столбец «Итоги» (граница: после него промежуточных столбцов быть не должно) */
    public function totalColumn(): ?Column
    {
        return $this->columns()->where('type', 'total')->orderBy('sort_order')->first();
    }

    /** Столбцы-модули 1..3 по порядку */
    public function moduleColumns()
    {
        return $this->columns()->where('type', 'module')->orderBy('sort_order')->get();
    }

    /**
     * Пересчитать баллы модулей (и итога) для одного студента
     * по настройкам суммирования (sum_into столбцов + флаги группы).
     */
    public function recomputeForUser(int $userId): void
    {
        $modules = $this->moduleColumns()->values();   // [0]=1 модуль, [1]=2, [2]=3
        $studentGrades = $this->grades()->where('user_id', $userId)->get()->keyBy('column_id');

        foreach ([1, 2, 3] as $n) {
            if (! $this->{'sum_m'.$n}) continue;
            $moduleCol = $modules[$n - 1] ?? null;
            if (! $moduleCol) continue;

            // нет настроенных суммируемых столбцов — модуль ведётся вручную
            if (! $this->columns()->where('sum_into', $n)->exists()) continue;

            // пересчитываем ВСЕГДА: очистили все к/р — модуль станет пустым
            $parts = $this->grades()->where('user_id', $userId)
                ->whereHas('column', fn ($q) => $q->where('sum_into', $n))
                ->get()
                ->filter(fn ($g) => is_numeric(trim($g->value)));

            $sum = $parts->sum(fn ($g) => (float) $g->value);
            Grade::updateOrCreate(
                ['group_id' => $this->id, 'user_id' => $userId, 'column_id' => $moduleCol->id],
                ['value' => $parts->isEmpty() ? '' : (string) (fmod($sum, 1) == 0 ? (int) $sum : $sum)]
            );
        }

        if ($this->sum_total) {
            $total = $this->totalColumn();
            if ($total) {
                // значения модулей берём ЗАНОВО из БД: они могли быть
                // только что пересчитаны выше в этом же вызове
                $freshMods = $this->grades()->where('user_id', $userId)
                    ->whereIn('column_id', $modules->filter()->pluck('id'))
                    ->pluck('value', 'column_id');
                $modSum = $modules->filter()->sum(function ($mc) use ($freshMods) {
                    $v = trim((string) ($freshMods[$mc->id] ?? ''));
                    return is_numeric($v) ? (float) $v : 0;
                });
                Grade::updateOrCreate(
                    ['group_id' => $this->id, 'user_id' => $userId, 'column_id' => $total->id],
                    ['value' => (string) (fmod($modSum, 1) == 0 ? (int) $modSum : $modSum)]
                );
            }
        }
    }

    /** Пересчитать для всех студентов группы */
    public function recomputeAll(): void
    {
        foreach ($this->students()->pluck('users.id') as $uid) {
            $this->recomputeForUser((int) $uid);
        }
    }

    /** Разбор названия «ПИб-231» → [спец, уровень, год, номер] */
    public static function parseName(string $name): array
    {
        $m = [];
        if (preg_match('/^([A-Za-zА-Яа-я]+)([бмБМ])-?(\d{2})(\d)$/', trim($name), $m)) {
            return [
                'level'  => mb_strtolower($m[2]) === 'м' ? 'Магистратура' : 'Бакалавриат',
                'year'   => 2000 + (int)$m[3],
                'number' => (int)$m[4],
            ];
        }
        return ['level' => 'Бакалавриат', 'year' => date('Y'), 'number' => 1];
    }
}
