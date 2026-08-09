<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['grade_id','user_id','text','image','file'];

    public function grade()   { return $this->belongsTo(Grade::class); }
    public function author()  { return $this->belongsTo(User::class, 'user_id'); }
}
