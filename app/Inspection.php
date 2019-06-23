<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $table = "inspections";
    
    protected $casts = [
        'inspection_time' => 'datetime:d-M-Y',
    ];
}
