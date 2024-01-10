<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    protected $hidden = ['updated_at', 'pivot'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');

    }

    public function scopeByGroup($query,$id){
        return $query->where('id',$id);
    }

    public function scopeWithUser($query){
        return $query->with(['User'=>function($q){
            $q->select('id','name','email');
        }]);
    }

    public function members(){

        return $this->belongsToMany('App\Models\User', 'user_groups', );
    }



    public function files()
    {
        return $this->hasMany(File::class);
    }
}
