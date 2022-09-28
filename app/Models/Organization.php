<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'tbl_organization';


    public function users()
    {
        return $this->hasMany(User::class, 'organization_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(OrganizationType::class, 'type_id', 'id');
    }
}
