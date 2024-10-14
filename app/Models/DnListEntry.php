<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DnListEntry extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_dn_list_entry';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'id',
        'dn_list_id',
        'first_name',
        'middle_name',
        'last_name',
        'birthday',
        'enp',
        'snils',
        'description',
        'contact_info',
        'status_id',
        'user_id',
    ];

    public function getIncrementing()
    {
        return false;
    }
    public function getKeyType()
    {
        return 'string';
    }

}
