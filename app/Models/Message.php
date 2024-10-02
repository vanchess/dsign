<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\MessageToUsers;

class Message extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'tbl_msg';
    //protected $with = ['to','files'];

    public function status()
    {
        return $this->belongsTo(MessageStatus::class, 'status_id', 'id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function period()
    {
        return $this->belongsTo(Period::class, 'period_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(MessageType::class, 'type_id', 'id');
    }

    public function category()
    {
        return $this->belongsToMany(MessageCategory::class, 'tbl_msg_category_link', 'msg_id', 'category_id');
    }

    public function from()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function to()
    {
        return $this->belongsToMany(User::class, 'tbl_msg_to_users', 'msg_id', 'user_id')->using(MessageToUsers::class);
    }

    public function files()
    {
        return $this->belongsToMany(File::class, 'tbl_msg_files', 'msg_id', 'file_id');
    }

    public function displists()
    {
        return $this->hasMany(DispList::class, 'msg_id', 'id');
    }

    public function dnContract()
    {
        return $this->hasMany(DispList::class, 'msg_id', 'id');
    }
}
