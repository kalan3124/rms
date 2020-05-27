<?php

namespace App\Models;

class InstitutionCategory extends Base
{
    protected $table = 'institution_category';

    protected $primaryKey = 'ins_cat_id';

    protected $fillable = [
        'ins_cat_name','ins_cat_short_name'
    ];

    protected $codeName = 'ins_cat_short_name';

    public function institution (){
        return $this->hasMany(Institution::class,'ins_cat_id','ins_cat_id');
    }
}
