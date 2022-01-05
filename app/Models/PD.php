<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PD extends Model
{
    protected $table = 'pd';
    
    protected $casts = [
        'p_date' => 'datetime',
        'birthday' => 'datetime',
    ];
}
