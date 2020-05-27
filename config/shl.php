<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Privileged user types for medical app
    |--------------------------------------------------------------------------
    |
    | Please put the medical representative user type ids below. It will enables
    | the user type to using medical app.
    |
    */
    'medical_app_privileged_user_types'=>explode(',',env('MEDICAL_APP_PRIVILEGED_USER_TYPES','3')),
    'sales_app_privileged_user_types'=>explode(',',env('SALES_APP_PRIVILEGED_USER_TYPES','10')),
    /*
    |--------------------------------------------------------------------------
    | Field Manager Type
    |--------------------------------------------------------------------------
    |
    | This variable is checking when you searching for a field manager.
    |
    */
    'field_manager_type'=>env('FIELD_MANAGER_TYPE','2'),
    /*
    |--------------------------------------------------------------------------
    | Medical Rep Type
    |--------------------------------------------------------------------------
    |
    | This variable is checking when you searching for a medical rep.
    |
    */
    'medical_rep_type'=>env('MEDICAL_REP_TYPE','3'),

    /*
    |--------------------------------------------------------------------------
    | Product Specialist
    |--------------------------------------------------------------------------
    |
    | Same as medical reps
    |
    */
    'product_specialist_type'=>env('PRODUCT_SPECIALIST_TYPE','5'),
    /*
    |--------------------------------------------------------------------------
    | Head of Department Type
    |--------------------------------------------------------------------------
    |
    | This variable is checking when you searching for a head of department.
    |
    */
    'head_of_department_type'=>env('HEAD_OF_DEPARTMENT_TYPE','4'),
    /*

    |--------------------------------------------------------------------------
    | This variable is checking when you searching for a sales representative.
    |--------------------------------------------------------------------------
    |
    */
    'sales_rep_type'=>env('SALES_REP_TYPE','10'),
    /*

    |--------------------------------------------------------------------------
    | This variable is checking when you searching for a area sales manager.
    |--------------------------------------------------------------------------
    |
    */
    'area_sales_manager_type'=>env('AREA_SALES_MANAGER_TYPE','13'),
    /*
    |--------------------------------------------------------------------------
    | This variable is checking when you searching for a sales representative.
    |--------------------------------------------------------------------------
    |
    */
    'distributor_sales_rep_type'=>env('DISTRIBUTOR_SALES_REP_TYPE','15'),
    /*
    |--------------------------------------------------------------------------
    | This variable is checking when you searching for a distributors.
    |--------------------------------------------------------------------------
    |
    */
    'distributor_type'=>env('DISTRIBUTOR_TYPE','14'),
    /*
    |--------------------------------------------------------------------------
    | Unproductive Reason Type , Sampling ....
    |--------------------------------------------------------------------------
    |
    | All reasons filter by following four variables when
    | sending reasons to android app
    |
    */
    'unproductive_reason_type'=>env('UNPRODUCTIVE_REASON_TYPE','4'),
    'sampling_reason_type'=>env('SAMPLING_REASON_TYPE','1'),
    'detailing_reason_type'=>env('DETAILING_REASON_TYPE','2'),
    'promotion_reason_type'=>env('PROMOTION_REASON_TYPE','3'),
    'expenses_reason_type'=>env('EXPENSES_REASON_TYPE','5'),
    'bata_reason_type'=>env('BATA_REASON_TYPE','6'),
    /*
    |--------------------------------------------------------------------------
    | Proxy address
    |--------------------------------------------------------------------------
    |
    | This proxy is using to http requests from backend
    |
    */
    "proxy_address"=>env("SHL_PROXY",null),
    /**
     * ------------------------------------------------------------------------
     * Color codes
     * ------------------------------------------------------------------------
     *
     * Color codes for day types and others.
     *
     */
    'color_codes'=>[
        1=>"Red",
        2=>"Green",
        3=>"Blue",
        4=>"Black",
        5=>"Yellow",
        6=>"Purple",
        7=>"Brown",
        8=>"Orange"
    ],

    'months'=>[
        1	=>"January",
        2	=>"Februar",
        3	=>"March",
        4	=>"April",
        5	=>"May",
        6	=>"June",
        7	=>"July",
        8	=>"August",
        9	=>"September",
        10	=>"October",
        11	=>"November",
        12	=>"December"
    ],

     /*
    |--------------------------------------------------------------------------
    | This variable is Pg 01 Sale price List default id
    |--------------------------------------------------------------------------
    |
    */
    'pg01_auto_increment_id'=>env('PG01_AUTO_INCREMENT_ID',null),

     /*
    |--------------------------------------------------------------------------
    | Vat Percentage for secondary invoices
    |--------------------------------------------------------------------------
    |
    */
    'vat_percentage'=>env('VAT_PERCENTAGE',8),

    'system_email'=> 'oneforce@sunshinehealthcare.lk'
];
