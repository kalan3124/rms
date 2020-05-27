<?php

namespace App\Ext;

use App\Models\Base;

class Customer extends Base
{
    protected $table = 'ext_customer_uiv';

    protected $revisionEnabled = false;

    protected $primaryKey = 'ifs';

    protected $fillable = [
        'customer_id',
        'name',
        'default_language',
        'country_code',
        'country_name',
        'address_identity',
        'address1',
        'address2',
        'zip_code',
        'city',
        'city_name',
        'county',
        'county_name',
        'state',
        'state_name',
        'phone',
        'fax',
        'email',
        'delivery_terms',
        'delivery_description',
        'district',
        'district_description',
        'region',
        'region_description',
        'ship_via',
        'ship_via_description',
        'customer_group',
        'customer_group_description',
        'payment_term',
        'peyment_term_description',
        'tax_code',
        'numeration_group',
        'no_of_invoice_copies',
        'payment_method',
        'payment_method_description',
        'credit_analyst',
        'credit_limit',
        'credit_blocked',
        'allowed_overdue_days',
        'allowed_overdue_amount',
        'cust_stat_group',
        'cust_price_grp',
        'cust_price_grp_description',
        'salesman',
        'salesman_name',
        'market',
        'market_description',
        'currency',
        'credit_control_group',
        'type',
        'type_db',
        'order_type',
        'order_type_description',
        'priority',
        'sfa_price_list',
        'sfa_price_list_description',
        'sfa_customer_type',
        'sfa_customer_type_description',
        'cust_class',
        'cust_class_description',
        'association_no',
        'cust_inactive_date',
        'last_updated_on'


    ];
}
