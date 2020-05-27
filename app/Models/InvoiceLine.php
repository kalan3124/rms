<?php

namespace App\Models;

use App\Models\Base;
use App\Models\Product;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class InvoiceLine extends Base
{
    protected $table = 'invoice_line';

    protected $primaryKey = 'inv_line_id';

    protected $fillable = [
        'company',
        'invoice_id',
        'item_id',
        'party_type',
        'series_id',
        'invoice_no',
        'client_state',
        'identity',
        'name',
        'invoice_date', 
        'currency',     
        'vat_code',     
        'vat_rate',
        'vat_curr_amount',
        'net_curr_amount',
        'gross_curr_amount',
        'net_dom_amount',
        'vat_dom_amount',
        'reference',     
        'order_no',       
        'line_no',      
        'release_no',      
        'line_item_no',      
        'pos',      
        'contract',     
        'catalog_no',     
        'description',     
        'taxable_db',      
        'invoiced_qty',      
        'sale_um',
        'price_conv',     
        'price_um',      
        'sale_unit_price',
        'unit_price_incl_tax',
        'discount',
        'order_discount',
        'customer_po_no',     
        'rma_no',     
        'rma_line_no',     
        'rma_charge_no',     
        'additional_discount',
        'configuration_id',      
        'delivery_customer',      
        'series_reference',     
        'number_reference',     
        'invoice_type',     
        'prel_update_allowed',         
        'man_tax_liability_date',        
        'payment_date',      
        'prepay_invoice_no',      
        'prepay_invoice_series_id',      
        'assortment_node_id',     
        'charge_percent',
        'charge_percent_basis',      
        'bonus_part',     
        'total order line discount %',
        'total order line discount amt',
        'city',     
        'salesman_code',     
        'salesman_name',     
        'odering_region',        
        'last_updated_on',
        'inv_head_id',
        'product_id',
        'chemist_id'
    ];

    public function price(){
        return $this->belongsTo(SalesPriceList::class,'catalog_no','catalog_no')->where('price_list_no','=','BDGT');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function invoice(){
        return $this->belongsTo(Invoice::class,'inv_head_id','inv_head_id');
    }

    public function chemist(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }

    /**
     * Bind the sales allocation
     *
     * @param Builder $query
     * @param int $userId
     * @param boolean $return
     * @return Builder
     */
    public static function bindSalesAllocation($query,$userId,$return = false){

        $tableName = $return?'rl':'il';

        $query->crossJoin('users AS sau');
        $query->where('sau.id',$userId);

        $query->leftJoin('tmp_sales_allocation AS sa',function($query) use($tableName,$userId){
            $query->where('sa.chemist_id',DB::raw("$tableName.chemist_id"));
            $query->where('sa.product_id',DB::raw("$tableName.product_id"));
            $query->where('sa.u_id',$userId);
            // $query->where('sa.tsa_percent','>','0');
            $query->whereNull('sa.deleted_at');
        });

        if($return)
            $query->leftJoin('tmp_invoice_allocation AS ia',function($query) use($tableName,$userId){
                $query->where('ia.return_line_id',DB::raw("$tableName.return_line_id"));
                $query->where('ia.u_id',$userId);
                $query->whereNull('ia.deleted_at');
            });
        else
            $query->leftJoin('tmp_invoice_allocation AS ia',function($query) use($tableName,$userId){
                $query->where('ia.inv_line_id',DB::raw("$tableName.inv_line_id"));
                $query->where('ia.u_id',$userId);
                $query->whereNull('ia.deleted_at');
            });

        return $query;
    }

    public static function salesAmountColumn($outputName,$return=false){

        $qtyColumn = $return?"IFNULL(ia.tia_qty,rl.invoiced_qty)":"IFNULL(ia.tia_qty,il.invoiced_qty)";
        
        $actualPriceColumn = $return?'rl.sale_unit_price':'il.sale_unit_price';

        return DB::raw("ROUND(SUM(IF(sau.divi_id=1,$actualPriceColumn,IFNULL(IF(pi.lpi_bdgt_sales=\"0.00\",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0)) * IFNULL((IFNULL(sa.tsa_percent,100)/100) * $qtyColumn,0)) ,2) AS $outputName");
    }

    public static function salesQtyColumn($outputName,$return=false,$round=true){
        $qtyColumn = $return?"IFNULL(ia.tia_qty,rl.invoiced_qty)":"IFNULL(ia.tia_qty,il.invoiced_qty)";

        if($round){

            return DB::raw(
                "ROUND(Ifnull(
                    Sum(
                        (IFNULL(sa.tsa_percent,100)/100)
                        *
                        $qtyColumn
                    )
                    ,
                    0
                    )) AS $outputName");
        } else {
            return DB::raw(
                "Ifnull(
                    Sum(
                        (IFNULL(sa.tsa_percent,100)/100)
                        *
                        $qtyColumn
                    )
                    ,
                    0
                    ) AS $outputName");
        }
    }

    public static function whereWithSalesAllocation($query,$columnName,$value){
        $array = true;
        if(is_string($value)||is_numeric($value))
            $array = false;

        $query->where(function($query) use($columnName,$value,$array){
            if($array)
                $query->orWhereIn($columnName,$value);
            else
                $query->orWhere($columnName,$value);

            $query->orWhereNotNull('ia.tia_qty');
            $query->orWhereNotNull('sa.tsa_percent');
        });

        return $query;
    }
}
