<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DispListEntry extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'tbl_displist_entry';


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
        'displist_id',
        'first_name',
        'middle_name',
        'last_name',
        'birthday',
        'enp',
        'snils',
        'preventive_medical_measure_id',
        'description',
        'contact_info',
        'status_id',
        'user_id',
        'organization_id'
    ];

    protected $casts = [

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
