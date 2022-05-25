<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'tbl_files';
    // protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'file_path'
    ];
    
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function messages()
    {
        return $this->belongsToMany(Message::class, 'tbl_msg_files', 'file_id', 'msg_id');
    }
    
    public function signs()
    {
        return $this->hasMany(FileSign::class, 'file_id', 'id');
    }
    
    
    public function signCerts()
    {
        return $this->belongsToMany(CryptoCert::class, 'tbl_file_sign', 'file_id', 'cert_id')->wherePivot('deleted_at', null)->wherePivot('verified_on_server_success', true);
    }
    
    public function signUsers()
    {
        return $this->belongsToMany(User::class, 'tbl_file_sign', 'file_id', 'user_id')->wherePivot('deleted_at', null);
    }
}