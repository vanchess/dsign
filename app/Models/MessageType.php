<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageType extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'title',
        'description'
    ];

    protected $dates = ['deleted_at'];
    protected $table = 'tbl_msg_type';
    
    public function messages()
    {
        return $this->hasMany(Message::class, 'type_id', 'id');
    }
}
