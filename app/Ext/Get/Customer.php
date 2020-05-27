<?php

namespace App\Ext\Get;

use App\Models\Chemist;
use App\Ext\Get\HasModel\Row;
use App\Models\SubTown;
use App\Ext\Get\HasModel\Model;
use App\Models\ChemistMarketDescription;
use App\Models\Institution;
use App\Models\InstitutionCategory;

class Customer extends Model
{
    protected $table = 'ifsapp.EXT_CUSTOMER_UIV';

    protected $primaryKey = 'customer_id';

    public $originalModelName = Chemist::class;

    public $codeName = 'customer_id';

    public $columnMapping = [
        'name'=>'chemist_name',
        'address1'=>'chemist_address'
    ];

    public function __construct()
    {

        $marketDescription = new Row(ChemistMarketDescription::class,'market_description',[
            'chemist_mkd_name'=>'market_description'
        ]);

        $subTown = new Row(SubTown::class,'city',[
            // 'sub_twn_name'=>'city_name',
            // 'twn_id'=>new Row(Town::class,'county',[
            //     'twn_name'=> 'county_name',
            //     'twn_short_name'=> 'county_name',
            //     'ar_id'=>new Row(Area::class,'region',[
            //         'ar_name'=>'region_description',
            //     ])
            //  ])
        ]);

        $this->subModels = [
            'chemist_mkd_id'=>$marketDescription,
            'sub_twn_id'=>$subTown
        ];
    }

    protected function createOrUpdate($data,$inst=null){
        parent::createOrUpdate($data,$inst);

        if(\in_array($data['customer_group'],['T_PVT','T_INS'])){

            $institution = Institution::where('ins_code' ,$data['customer_id'])->first();

            $category = InstitutionCategory::where('ins_cat_short_name',$data['customer_group'])->first();
            $subTown = SubTown::where('sub_twn_code',$data['city'])->first();

            if($institution){
                $institution->ins_name = $data['name'];
                $institution->ins_address = $data['address1'].','.$data['address2'];
                $institution->ins_cat_id = $category?$category->getKey():null;
                $institution->sub_twn_id = $subTown?$subTown->getKey():null;
                $institution->save();
            } else {
                Institution::create([
                    'ins_name' => $data['name'],
                    'ins_code'=> $data['customer_id'],
                    'ins_short_name' => $data['customer_id'],
                    'ins_address' => $data['address1'].','.$data['address2'],
                    'ins_cat_id' => $category?$category->getKey():null,
                    'sub_twn_id' => $subTown?$subTown->getKey():null,
                ]);
            }
        }

    }

}

