<?php

namespace App\Models;

class CompetitorDetails extends Base
{
    protected $table = 'competitors_details';

    protected $primaryKey = 'com_details_id';

    protected $fillable = [
        'com_survey_id',
        'cmp_id',
        'total_purchase_value',
        'visit_frequency',
        'visit_day_Of_week'
    ];
}
