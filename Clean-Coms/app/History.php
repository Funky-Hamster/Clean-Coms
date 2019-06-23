<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = "histories";
    
    protected $casts = [
        'created_at' => 'datetime:d-M-Y H:i:s',
    ];
}