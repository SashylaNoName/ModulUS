<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'invite_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* ===== Вспомогательные методы ролей ===== */
    public function isTeacher(): bool { return $this->role === 'teacher'; }
    public function isStudent(): bool { return $this->role === 'student'; }

    /** Инициалы для аватара (из ФИО) */
    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
    }

    /* ===== Связи ===== */
    public function subjects()       { return $this->hasMany(Subject::class); }       // у преподавателя
    public function groups()         { return $this->hasMany(Group::class); }         // у преподавателя
    public function groupsAsStudent(){ return $this->belongsToMany(Group::class); }   // у студента
    public function grades()         { return $this->hasMany(Grade::class); }
    public function comments()       { return $this->hasMany(Comment::class); }
    public function notifications()  { return $this->hasMany(Notification::class)->latest(); }
}
