<?php

namespace App\Models;

/**
 * Bata type
 * 
 * @property string $bt_name
 * @property string $bt_code
 * @property int $bt_type 0 = Main | 1 = Field Manager | 2 = MR/PS | 3 = Sales Rep | 4 = JENG | 5 = SENG | 6 = DSR
 * @property float $bt_value
 * @property int $divi_id
 * @property int $btc_id
 * 
 * @property Division $division
 * @property BataCategory $bataCategory
 */
class BataType extends Base
{
    protected $table = 'bata_type';

    protected $primaryKey = 'bt_id';

    protected $fillable = [
        'bt_name','bt_code','bt_type', 'bt_value','divi_id','btc_id'
    ];

    protected $codeName = 'bt_code';

    public function division(){
        return $this->belongsTo(Division::class,'divi_id','divi_id');
    }

    public function bata_category(){
        return $this->belongsTo(BataCategory::class,'btc_id','btc_id');
    }
    public function bataCategory(){
        return $this->bata_category();
    }
}
