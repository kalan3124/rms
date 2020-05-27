<?php

namespace App\Models;

class Principal extends Base
{
    protected $table = 'principal';

    protected $primaryKey = 'principal_id';

    protected $fillable = [
        'principal_code','principal_name'
    ];

    protected $codeName = 'principal_code';

    public function product_family(){
        return $this->hasMany(ProductFamily::class,'principal_id','principal_id');
    }
}
