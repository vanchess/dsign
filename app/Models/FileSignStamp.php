<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileSignStamp extends Model
{
    use SoftDeletes;
    
    protected $table = 'tbl_file_sign_stamp';
    
    protected $fillable = [
        'file_id',
        'pdf_file_path',
        'pdf_with_id_file_path',
        'stamped_file_path',
        'type_id',
        'user_id'
    ];
}
