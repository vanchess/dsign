<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invite extends Model
{
    use SoftDeletes;
    
    protected $table = 'tbl_invite';
    protected $fillable = [
        'invite',
        'organization',
        'options',
        'user_id'
    ];
    
    protected $casts = [
        'options' => 'array'
    ]; 
}