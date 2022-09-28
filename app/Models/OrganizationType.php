<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationType extends Model
{
    //use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'tbl_organization_type';


    public function organizations()
    {
        return $this->hasMany(User::class, 'organization_id', 'id');
    }
}
