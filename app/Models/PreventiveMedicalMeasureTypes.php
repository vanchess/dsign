<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreventiveMedicalMeasureTypes extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'tbl_preventive_medical_measure_types';

}
