<?php

Route::post('/login','UserController@login');

// If user is authenticated and authenticated user is privileged to login via sales app
Route::group(['middleware'=>['auth:api','is_sales_rep']],function(){
    Route::group(['middleware'=>'log'], function () {

            Route::post('/user_details','UserController@userDetails');
            Route::post('/dayStatus', 'UserAttendanceController@dayStatus');
            // Route::post('attendance','UserAttendanceController@attendance');

            Route::group(['prefix' => 'attendance'], function () {
                Route::post('save','UserAttendanceController@attendance');
            });
            //check new APK Version
            Route::post('/getLatestApp', 'AndroidApkController@checkVersion');

            Route::post('principals', 'ProductController@product_principals');
            Route::post('product_family', 'ProductController@get_product_family');
            Route::post('products', 'ProductController@get_products');
            Route::post('principals_seq', 'ProductController@addSeqToPrinciple');

            Route::post('price_groups', 'ProductController@get_price_group');
            Route::post('product_batches', 'ProductController@get_product_batches');

            Route::post('sync_orders', 'SalesOrderController@save');
            Route::post('daily_orders', 'SalesOrderController@dailySaleOrders');
            Route::post('last_orders', 'SalesOrderController@getLastOrders');

            Route::post('month_plan', 'ItineraryController@monthlyItinerary');

            Route::post('reasons','ReasonController@getUnproductiveReason');

            Route::post('sr_chemist','SrChemistController@srChemistData');

            Route::post("/gps/save","GPSController@save");
            Route::post("/gps/statusChange","GPSController@statusChange");

            Route::group(['prefix' => 'unproductive'], function (){
                Route::post('save','UnproductiveController@save');
                Route::post('daily_unproductive','UnproductiveController@getDailyUnPro');
            });

            Route::group(['prefix' => 'returnNote'], function (){
                Route::post('save','ReturnNoteController@save');
            });

            Route::post('/chemist', 'ChemistController@chemists');
            Route::post('unplanned_chemist', 'ChemistController@unplanned_chemist');

            Route::post('target_achievement', 'TargetController@getSrTarget');
            Route::post('credit_limit', 'ChemistOutstandingController@getChemistOutstand');

            Route::post('/bataType/all', 'BataTypeController@getBataTypes');
            Route::post('/bataExpenses/save', 'BataTypeController@saveBataExpenses');

            Route::post('/suggest_order', 'SuggestiveOrderController@createSuggestOrder');

            Route::post('/load_competitors', 'CompetitorsController@loadCompetitors');
            Route::post('/market_survey', 'CompetitorsController@saveDetails');
            Route::post('/valid_date', 'CompetitorsController@loadValid');

        });
    }
);

