<?php

namespace App\Models;

/**
 * Company Return Line Model
 *
 * @property int $crl_id AI Key
 * @property int $cr_id Company Return Head Id
 * @property int $grnl_id Good Note Received Line Id
 * @property int $product_id Product Id
 * @property int $db_id Distributor Batch Id
 * @property int $crl_qty Qty
 * @property int $rsn_id Reason Id
 * @property int $crl_salable Salable Status
 *
 * @property CompanyReturn $companyReturn
 * @property GoodReceivedNoteLine $goodReceivedNoteLine
 * @property Product $product
 * @property DistributorBatch $batch
 * @property Reason $reason
 */

class CompanyReturnLine extends Base {
    protected $table = 'company_return_line';

    protected $primaryKey = 'crl_id';

    protected $fillable = [
        'cr_id',
        'grnl_id',
        'product_id',
        'db_id',
        'crl_qty',
        'rsn_id',
        'crl_salable',
    ];

    public function companyReturn(){
        return $this->belongsTo(CompanyReturn::class,'cr_id','cr_id');
    }

    public function goodReceivedNoteLine(){
        return $this->belongsTo(GoodReceivedNoteLine::class,'grnl_id','grnl_id');
    }

    public function product(){
        return $this->belongsTo(Product::class, 'product_id','product_id');
    }

    public function batch(){
        return $this->belongsTo(DistributorBatch::class,'db_id','db_id');
    }

    public function reason(){
        return $this->belongsTo(Reason::class,'rsn_id','rsn_id');
    }
}
