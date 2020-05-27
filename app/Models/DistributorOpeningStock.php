<?php
namespace App\Models;

/**
 * Distributor opening stock
 * 
 * @property int $dis_id
 * @property int $dos_id
 * 
 * @property User $user
 */
class DistributorOpeningStock extends Base {
    protected $table = 'distributor_opening_stock';

    protected $primaryKey = 'dos_id';

    protected $fillable = [
        'dis_id'
    ];

    public function user(){
         return $this->belongsTo(User::class,'dis_id','id');
    }

    public function getCode()
    {
        return 'OS/'.$this->getKey();
    }
}