<?php
namespace App\Ext;

use App\Models\Base;

class InvoiceLineDelivery extends Base {
    protected $revisionEnabled = false;
    
    protected $table = 'ext_invoice_line_delivery_uiv';

    protected $primarykey = 'dist_invoice_line_id';

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
        'order_no',
        'line_no',
        'release_no',
        'line_item_no',
        'pos',
        'contract',
        'catalog_no',
        'description',
        'invoiced_qty',
        'sale_um',
        'price_conv',
        'price_um',
        'sale_unit_price',
        'unit_price_incl_tax',
        'customer_po_no',
        'rma_no',
        'rma_line_no',
        'rma_charge_no',
        'configuration_id',
        'delivery_customer',
        'invoice_type',
        'prel_update_allowed',
        'bonus_part',
        'salesman_code',
        'salesman_name',
        'odering_region',
        'part_no',
        'location_no',
        'lot_batch_no',
        'serial_no',
        'waiv_dev_rej_no',
        'qty_shipped',
        'delnote_no',
        'last_updated_on',
        'expiration_date'
    ];

}