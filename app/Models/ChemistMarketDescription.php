<?php

namespace App\Models;

class ChemistMarketDescription extends Base
{
    protected $table = "chemist_market_description";  

    protected $primaryKey = 'chemist_mkd_id';

    protected $fillable=[
        'chemist_mkd_name','chemist_mkd_code'
    ];

    protected $codeName = 'chemist_mkd_code';

    public function chemist(){
        return $this->hasMany(Chemist::class,'chemist_mkd_id','chemist_mkd_id');
    }
}
