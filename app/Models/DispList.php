<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DispList extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'tbl_displist';

    public function entries()
    {
        return $this->hasMany(DispListEntry::class, 'displist_id', 'id');
    }

    public function message()
    {
        return $this->belongsTo(Message::class, 'msg_id', 'id');
    }
}
