<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
/**
 * Distributor Stock
 *
 * @property int $dis_id
 * @property int $product_id
 * @property int $db_id
 * @property int $ds_credit_qty Plus
 * @property int $ds_debit_qty Minus
 * @property int $ds_ref_id
 * @property int $ds_ref_type
 *  1 = Goods Received Order Line |
 *  2 = Invoice Line |
 *  3 = Stock Ajusment Line |
 *  4 = Stock Write Off Line|
 *  5 = Opening Balance |
 *  6 = Invoice Bonus Line |
 *  7 = Return Line |
 *  8 = Return Bonus Line |
 *  9 = Company Return Line |
 *
 * @property DistributorBatch $batch
 * @property User $distributor
 * @property Product $product
 */
class DistributorStock extends Base {
    protected $table = 'distributor_stock';

    protected $primaryKey = 'ds_id';

    protected $fillable = [
        'dis_id',
        'product_id',
        'db_id',
        // +
        'ds_credit_qty',
        // -
        'ds_debit_qty',
        'ds_ref_id',
        'ds_ref_type'
    ];

    public function product(){
        return $this->belongsTo(Product::class, 'product_id','product_id');
    }

    public function distributor(){
        return $this->belongsTo(User::class, 'dis_id','id');
    }

    public function batch(){
        return $this->belongsTo(DistributorBatch::class,'db_id','db_id');
    }

    /**
     * Checking the stock for product or batch
     *
     * @param int $distId Distributor id
     * @param int $productId
     * @param int $batchId If this parameter not supplied, Returning the all stock for the product
     * @param string $date You can check the stock for a specified date.
     *
     * @return int
     */
    public static function checkStock(int $distId,int $productId, $batchId = null,$date=null){
        $query = DB::table('distributor_stock AS ds')->join('distributor_batches AS db','db.db_id','=','ds.db_id');

        $query->where('ds.product_id',$productId);
        $query->where('db.db_expire','>=',date('Y-m-d'));
        $query->where('ds.dis_id',$distId);

        if($batchId)
            $query->where('ds.db_id',$batchId);

        if($date)
            $query->whereDate('ds.created_at','<',$date);

        return $query->sum(DB::raw('ds_credit_qty - ds_debit_qty'));
    }

    /**
     * Get the reference parent
     *
     * @return GoodReceivedNote|DistributorInvoice|StockAdjusment|StockWriteOff|DistributorOpeningStock|DistributorReturn
     */
    public function getRefParent(){
        switch ($this->ds_ref_type) {
            case 1:
                /** @var GoodReceivedNoteLine $grnLine */
                $grnLine = GoodReceivedNoteLine::with('goodReceivedNote')->withTrashed()->find($this->ds_ref_id);
                return $grnLine?$grnLine->goodReceivedNote:null;
            case 2:
                /** @var DistributorInvoiceLine $invoiceLine */
                $invoiceLine = DistributorInvoiceLine::with('invoice')->withTrashed()->find($this->ds_ref_id);
                return $invoiceLine?$invoiceLine->invoice:null;
            case 3:
                /** @var StockAdjusmentProduct $stockAdjProduct */
                $stockAdjProduct = StockAdjusmentProduct::with('stockAdjustment')->withTrashed()->find($this->ds_ref_id);
                return $stockAdjProduct?$stockAdjProduct->stockAdjustment:null;
            case 4:
                /** @var StockWriteOffProduct $stockWriteOffProduct */
                $stockWriteOffProduct = StockWriteOffProduct::with('writeOff')->withTrashed()->find($this->ds_ref_id);
                return $stockWriteOffProduct?$stockWriteOffProduct->writeOff:null;
            case 5:
                return DistributorOpeningStock::find($this->ds_ref_id);
            case 6:
                /** @var DistributorInvoiceBonusLine $bonusLine */
                $bonusLine = DistributorInvoiceBonusLine::with('distributorInvoice')->withTrashed()->find($this->ds_ref_id);
                return $bonusLine?$bonusLine->distributorInvoice:null;
            case 7:
                /** @var DistributorReturnItem $returnLine */
                $returnLine = DistributorReturnItem::with('distributorReturn')->withTrashed()->find($this->ds_ref_id);
                return $returnLine?$returnLine->distributorReturn:null;
            case 8:
                /** @var DistributorReturnBonusItem $returnBonusLine */
                $returnBonusLine = DistributorReturnBonusItem::with('distributorReturn')->withTrashed()->find($this->ds_ref_id);
                return $returnBonusLine?$returnBonusLine->distributorReturn:null;
            case 9:
                /** @var CompanyReturnLine $returnLine */
                $returnLine = CompanyReturnLine::with('companyReturn')->withTrashed()->find($this->ds_ref_id);
                return $returnLine?$returnLine->companyReturn:null;
            default:
                return null;
        }
    }

    /**
     * Get the reference type
     *
     * @return string
     */
    public function getRefType(){
        switch ($this->ds_ref_type) {
            case 1:
                return "Good Received Note (GRN)";
            case 2:
                return "Sales Invoice (IN)";
            case 3:
                return "Stock Adjustment (SA)";
            case 4:
                return "Stock Write Off (SWO)";
            case 5:
                return "Opening Stock (OS)";
            case 6:
                return "Sales Invoice (IN)";
            case 7:
                return "Credit Note (CN)";
            case 8:
                return "Credit Note (CN)";
            case 9:
                return "Company Return (CR)";
            default:
                return null;
        }
    }

}
