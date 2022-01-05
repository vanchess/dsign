<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageCategory extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'title',
        'short_title',
        'type_id',
        'description',
        'order'
    ];

    protected $dates = ['deleted_at'];
    protected $table = 'tbl_msg_category';
    

}
