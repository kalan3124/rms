<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
/**
 * Returns from the distributor centers
 * 
 * @property float $discount
 * @property string $dist_return_number
 * @property int $dis_id
 * @property int $dsr_id
 * @property int $dc_id
 * @property string $return_date
 * @property int $di_id
 * 
 * @property DistributorReturnItem[]|Collection $lines
 * @property DistributorCustomer $distributorCustomer
 * @property User $distributor
 * @property User $distributorRep
 * @property DistributorInvoice $invoice
 */
class DistributorReturn extends Base
{
    protected $table = 'distributor_return';

    protected $primaryKey = 'dis_return_id';

    protected $codeName = 'dist_return_number';

    protected $fillable = [
        'discount',
        "dist_return_number",
        'dis_id',
        'dsr_id',
        'dc_id',
        'return_date',
        'di_id'
    ];

    /**
     * Generating a new invoice number
     *
     * @param int $disId
     * @param int $repId
     * @return string
     */
    public static function generateNumber($disId,$repId){
        $invoiceCount = self::where('dsr_id',$repId)->where('dis_id',$disId)->count();

        return 'RTN/'.$disId.'/'.$repId.'/'.($invoiceCount+1);
    }

    public function lines(){
        return $this->hasMany(DistributorReturnItem::class,'dis_return_id','dis_return_id');
    }

    public function distributorCustomer(){
        return $this->belongsTo(DistributorCustomer::class,'dc_id','dc_id');
    }

    public function distributor(){
        return $this->belongsTo(User::class,'dis_id','id');
    }

    public function distributorRep(){
        return $this->belongsTo(User::class,'dsr_id','id');
    }

    public function bonusLines(){
        return $this->hasMany(DistributorReturnBonusItem::class,'dis_return_id','dis_return_id');
    }

    public function invoice(){
        return $this->belongsTo(DistributorInvoice::class,'di_id','di_id');
    }
    
}
