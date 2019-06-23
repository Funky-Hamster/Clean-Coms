<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = "jobs";
    
    protected $casts = [
        'updated_at' => 'datetime:d-M-Y',
        'created_at' => 'datetime:d-M-Y',
    ];
}
