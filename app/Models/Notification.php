<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = ['user_id','type','icon','title','text','is_read'];

    protected $casts = ['is_read' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }

    public function scopeUnread($q) { return $q->where('is_read', false); }
}
