<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CryptoCert extends Model
{
    use SoftDeletes;
    
    protected $table = 'tbl_crypto_cert';
    
    protected $fillable = [
        'thumbprint',
        'serial_number',
        'validfrom',
        'validto',
        'CN',
        'SN',
        'G',
        'T',
        'OU',
        'O',
        'STREET',
        'L',
        'S',
        'C',
        'E',
        'OGRN',
        'SNILS',
        'INN',
        'issuer',
        'description'
    ];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'validfrom' => 'datetime',
        'validto' => 'datetime',
    ];
    
    public function signs()
    {
        return $this->hasMany(FileSign::class, 'cert_id', 'id');
    }
}
