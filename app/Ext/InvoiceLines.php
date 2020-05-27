<?php

namespace App\Ext;

use App\Models\Base;

class InvoiceLines extends Base
{
    protected $revisionEnabled = false;
    
    protected $table = 'ext_invoice_line_uiv';

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
        'last_updated_on' 
    ];

    public function price(){
        return $this->belongsTo(SalesPriceList::class,'catalog_no','catalog_no')->where('price_list_no','=','BDGT');
    }
}
