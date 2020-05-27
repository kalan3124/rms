<?php

namespace App\Ext;

use App\Models\Base;

class SalesPart extends Base
{
    protected $revisionEnabled = false;
    
    protected $table = 'ext_sales_part_uiv';

    protected $primaryKey = 'id';

    protected $fillable = [
        'ifs',
        'site',
        'sales_part_no',
        'sales_part_description',
        'part_no',
        'sales_group',
        'sales_group_desc',
        'sales_price_group',
        'sales_price_group_desc',
        'sales_uom',
        'active',
        'date_entered',
        'price',
        'price_incl_tax',
        'price_uom',
        'tax_code',
        'tax_code_desc',
        'last_updated_on',
        'bonus_part',
        'non_returnable',
        'short_description',
        'inv_part_no',
        'inv_part_desc',
        'unit_code',
        'accounting_group',
        'accounting_group_desc',
        'product_code',
        'product_code_desc',
        'product_family',
        'product_family_desc',
        'type_code',
        'moving_status',
        'hs_code',
        'atc_code',
        'device_type',
        'generic_name',
        'manufacturer_name',
        'pack_size',
        'part_approval_status',
        'part_type',
        'product_type',
        'product_valid_from',
        'self_life',
        'strength',
        'therapeutic_class'
    ];
}
