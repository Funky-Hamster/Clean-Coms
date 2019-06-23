<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = "companies";
    protected $hidden = [
        'is_deleted'
    ];
    
    protected $casts = [
        'inspection_time' => 'datetime:d-M-Y',
        'updated_at' => 'datetime:d-M-Y',
    ];
}
