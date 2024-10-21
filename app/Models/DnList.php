<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DnList extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'tbl_dn_list';

    public function entries()
    {
        return $this->hasMany(DnListEntry::class, 'dn_list_id', 'id');
    }

    public function message()
    {
        return $this->belongsTo(Message::class, 'msg_id', 'id');
    }
}
