<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    use HasFactory;


    protected $fillable =['user_id','group_id'];

    protected $hidden =['updated_at'];


    public function scopeByUser($query,$id){
        return $query->where('user_id',$id);
    }

    public function scopeByGroup($query,$id){
        return $query->where('group_id',$id);
    }
}
