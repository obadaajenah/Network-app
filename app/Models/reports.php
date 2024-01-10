<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reports extends Model
{
    use HasFactory;

    protected $fillable=[
        'file_id',
        'event_type',
        'event_date',
        'user_id',

    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }
}
