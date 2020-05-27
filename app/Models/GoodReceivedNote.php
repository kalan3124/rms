<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Goods received notes (GRN)
 * 
 * @property int $grn_id Primary Key
 * @property string $grn_no GRN Number
 * @property int $dis_id Distributor Id
 * @property int $dsr_id Distributor Rep Id. (This is not useful)
 * @property int $po_id Purchase order id associated with the GRN
 * @property float $grn_amount Amount of the GRN
 * @property string $grn_date
 * @property float $grn_org_amount
 * @property string $grn_confirmed_at
 * @property int $grn_confirmed_by
 * 
 * @property User $distributor
 * @property User $distributorSalesRep
 * @property PurchaseOrder $purchaseOrder
 * @property Collection|GoodReceivedNoteLine[] $lines
 * @property User $confirmedUser
 */
class GoodReceivedNote extends Base {
    protected $table = 'good_received_note';

    protected $primaryKey = 'grn_id';

    protected $codeName = 'grn_no';

    protected $fillable = [
        'grn_no',
        'dis_id',
        'dsr_id',
        'po_id',
        'grn_amount',
        'grn_date',
        'grn_org_amount',
        'grn_confirmed_at',
        'grn_confirmed_by'
    ];

    public function distributor(){
        return $this->belongsTo(User::class,'dis_id','id');
    }

    public function distributorSalesRep(){
        return $this->belongsTo(User::class,'dsr_id','id');
    }

    public function purchaseOrder(){
        return $this->belongsTo(PurchaseOrder::class,'po_id','po_id');
    }

    public function confirmedUser(){
        return $this->belongsTo(User::class,'grn_confirmed_by','id');
    }

    public function lines(){
        return $this->hasMany(GoodReceivedNoteLine::class,'grn_id','grn_id');
    }
}