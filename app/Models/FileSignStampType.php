<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileSignStampType extends Model
{
    use SoftDeletes;
    
    protected $table = 'tbl_file_sign_stamp_type';
    
    protected $fillable = [
        'name',
        'lable',
        'description'
    ];
}
