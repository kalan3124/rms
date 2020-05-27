<?php

namespace App\Models;

class CompetitorMarketSurvey extends Base
{
    protected $table = 'competitor_market_survey';

    protected $primaryKey = 'com_survey_id';

    protected $fillable = [
        'chemist_id',
        'survey_time',
        'lat',
        'lon',
        'battery',
        'owner_name',
        'contact_person',
        'contact_1',
        'contact_2',
        'email',
        'no_of_staff',
        'tot_pur_month',
        'pharmacy_pur_month',
        'val_shl_pro_thirdPartyDis',
        'val_tot_pro_Redistributed',
        'val_shl_pro_Redistributed',
        'pharmacy_sales_day',
        'pharmacy_sales_month',
        'remark',
        'activeStatus',
        'from',
        'to'
    ];
}
