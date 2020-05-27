<?php

namespace App\Models;

class ChemistClass extends Base
{
    protected $table = "chemist_class";  

    protected $primaryKey = 'chemist_class_id';

    protected $fillable=[
        'chemist_class_name'
    ];

    protected $codeName = 'chemist_class_name';

    public function chemist(){
        return $this->hasMany(Chemist::class,'chemist_class_id','chemist_class_id');
    }
}
