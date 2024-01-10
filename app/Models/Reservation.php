<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','file_id' ,'status' ,'type'];

    protected $hidden =['status','file_id','user_id','updated_at'];


    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByUser($query,$id){
        return $query->where('user_id',$id);
    }
    
    public function scopeByFile($query,$id){
        return $query->where('file_id',$id);
    }



    public function scopeNotActive($query){
        return $query->where('status','0');
    }

}
