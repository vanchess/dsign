<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalInstitution extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_mo';

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

}
