<?php

namespace App\Models;

class ChemistTypes extends Base
{
    protected $table = "chemist_types";  

    protected $primaryKey = 'chemist_type_id';

    protected $fillable=[
        'chemist_type_name','chemist_type_short_name'
    ];

    protected $codeName = 'chemist_type_short_name';

    public function chemist(){
        return $this->hasMany(Chemist::class,'chemist_type_id','chemist_type_id');
    }
}
