<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $fillable = ['name','subject_id','user_id','level','year','number','invite_token'];

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
