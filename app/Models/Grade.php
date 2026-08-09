<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['group_id','user_id','column_id','value'];

    public function group()   { return $this->belongsTo(Group::class); }
    public function student() { return $this->belongsTo(User::class, 'user_id'); }
    public function column()  { return $this->belongsTo(Column::class); }
    public function comments(){ return $this->hasMany(Comment::class)->oldest(); }
}
