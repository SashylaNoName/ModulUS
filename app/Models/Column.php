<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    protected $fillable = ['group_id','title','type','position','sum_into','hidden','sort_order'];

    protected $casts = ['hidden' => 'boolean'];

    public function group()  { return $this->belongsTo(Group::class); }
    public function grades() { return $this->hasMany(Grade::class); }
}
