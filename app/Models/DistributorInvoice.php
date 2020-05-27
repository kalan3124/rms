<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * # Distributor invoices model
 *
 * @property int $di_id Auto Increment Id
 * @property int $dist_order_id Sales Order Id
 * @property float $di_amount Invoice Gross Amount (Without Discount calculations)
 * @property float $di_discount Invoice Discount Amount
 * @property int $dsr_id Distributor Sales Rep Id
 * @property int $dis_id Distributor Id
 * @property string $di_number Invoice Number
 * @property int $dc_id Customer Id
 * @property string $di_printed_at Printed date and time
 * @property int $di_is_direct Is direct invoice (1=Yes, 2=No)
 * @property float $di_vat_percentage
 *
 * @property DistributorSalesOrder $salesOrder
 * @property User $distributor
 * @property User $distributorSalesRep (Salesman if direct invoice)
 * @property DistributorInvoiceLine[]|Collection $lines
 * @property DistributorCustomer $customer
 * @property DistributorInvoiceBonusLine[]|Collection $bonusLines
 *
 * ## Bonus Approval Feature Columns
 * @property string $di_approve_requested_at Bonus approval requested time (Also same to the created_at time)
 * @property int $di_approve_requested_by Bonus approval requested user
 * @property string $di_approved_at Bonus approval approved time
 * @property int $di_approved_by Bonus approval approved user
 * @property User $approvalRequestedUser
 * @property User $approvedUser
 * @property DistributorInvoiceUnapprovedBonusLine[]|Collection $unapprovedBonusLines
 *
 */
class DistributorInvoice extends Base {
    protected $table = 'distributor_invoice';

    protected $primaryKey = 'di_id';

    protected $codeName = 'di_number';

    protected $fillable = [
        'dist_order_id',
        'di_amount',
        'di_discount',
        'dsr_id',
        'dis_id',
        'di_number',
        'dc_id',
        'di_printed_at',
        'di_is_direct',
        'di_vat_percentage',
        'di_approve_requested_at',
        'di_approve_requested_by',
        'di_approved_at',
        'di_approved_by',
    ];

    public function salesOrder(){
        return $this->belongsTo(DistributorSalesOrder::class,'dist_order_id','dist_order_id');
    }

    public function distributor(){
        return $this->belongsTo(User::class,'dis_id','id');
    }

    public function distributorSalesRep(){
        return $this->belongsTo(User::class,'dsr_id','id');
    }

    public function customer(){
        return $this->belongsTo(DistributorCustomer::class,'dc_id','dc_id');
    }

    public function lines(){
        return $this->hasMany(DistributorInvoiceLine::class,'di_id','di_id');
    }

    public function bonusLines(){
        return $this->hasMany(DistributorInvoiceBonusLine::class,'di_id','di_id');
    }

    public function unapprovedBonusLines(){
        return $this->hasMany(DistributorInvoiceUnapprovedBonusLine::class,'di_id','di_id');
    }

    public function approvalRequestedUser(){
        return $this->belongsTo(User::class,'di_approve_requested_by','id');
    }

    public function approvedUser(){
        return $this->belongsTo(User::class,'di_approved_by','id');
    }

    /**
     * Generating a new invoice number
     *
     * @param int $disId
     * @return string
     */
    public static function generateNumber($disId){
        $invoiceCount = self::where('dis_id',$disId)->count();

        return 'INV/'.$disId.'/'.($invoiceCount+1);
    }
}
