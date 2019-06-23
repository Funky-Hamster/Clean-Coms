<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InspectionComment extends Model
{
    protected $table = "inspection_comments";
    protected $casts = [
        'created_at' => 'datetime:d-M-Y',
        'updated_at' => 'datetime:d-M-Y',
    ];
}
