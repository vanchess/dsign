<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageStatus extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'lable',
        'description'
    ];

    protected $dates = ['deleted_at'];
    protected $table = 'tbl_msg_status';
    
    public function messages()
    {
        return $this->hasMany(Message::class, 'status_id', 'id');
    }
}
