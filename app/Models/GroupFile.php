<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupFile extends Model
{
    use HasFactory;


    protected $fillable =['file_id','group_id'];

    protected $hidden =['updated_at'];



    public function scopeByGroup($query,$id){
        return $query->where('group_id',$id);
    }

    public function scopeByFile($query,$id){
        return $query->where('file_id',$id);
    }
}
