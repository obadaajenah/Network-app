<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class file extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status' , 'path','user_id' ];



    public function user(){

        return $this->belongsTo('App/user','user_id');

        }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);

    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }


    // public function groups(){
    //     return $this->belongsToMany('App\Models\File','group_files','group_id','file_id');
    // }

    public function scopeByuser($query,$id){
    return $query->where('user_id',$id);

    }
}
