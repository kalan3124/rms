<?php 
namespace App\Models;

class SfaWeeklyTarget extends Base{

    protected $table = 'sfa_weekly_target';

    protected $primaryKey = 'sfa_trg_wk_id';

    protected $fillable = [
        'u_id',
        'sfa_trg_id',
        'sfa_trg_year',
        'sfa_trg_month',
        'sfa_trg_week_no',
        'week_start_date',
        'week_end_date',
        'percentage',
        'amount',
    ];
}