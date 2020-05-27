<?php
Route::post('/login','UserController@login');


// If user is authenticated and authenticated user is privileged to login via medical app
Route::group(['middleware'=>['auth:api','is_medical_rep']],function(){
    Route::group(['middleware'=>'log'], function () {

            Route::post('/user_details','UserController@userDetails');
            Route::post('/attendance','UserAttendanceController@attendance');

            //check new APK Version
            Route::post('/getLatestApp', 'AndroidApkController@checkVersion');

            Route::group(['prefix' => 'unproductive'], function () {
                Route::post("/save","UnproductiveController@save");
            });

            Route::post("/reasons","CategoriesController@all");

            Route::group(['prefix' => 'productives'], function () {
                Route::post("/save","ProductiveController@save");
                Route::post("/previous", "ProductiveController@GetPreviousVisits");
            });

            Route::post("/gps/save","GPSController@save");
            Route::post("/gps/statusChange","GPSController@statusChange");

            Route::group(['prefix' => 'promotion'], function () {
                Route::post("/", "PromotionController@index");
                Route::post("/save", "PromotionController@save");
            });

            Route::group(['prefix' => 'doctortransport'], function () {
                Route::post("/save", "DoctorTransportController@save");
            });

            Route::post("/joinFieldVisit", "VisitsController@getJointFieldWorker");
            Route::post("/sampleProducts", "SampleProductController@index");
            Route::post("/visitTypes", "CategoriesController@visitTypes");
            Route::post('/dayStatus', 'UserAttendanceController@dayStatus');

            Route::post('/doctors', 'DoctorController@doctors');
            Route::post('/doctorClasses', 'DoctorController@doctorClasses');
            Route::post('/doctorSpecifications', 'DoctorController@doctorSpecifications');
            Route::post('/subTowns', 'DoctorController@getSubTowns');
            Route::post('/institutions', 'DoctorController@getInstitutions');
            Route::post('/saveDoctors', 'DoctorController@saveDoctors');

            Route::post('/chemist', 'ChemistController@chemists');
            Route::post('/otherHospitalStaff', 'OtherHospitalStaffController@otherHospitalStaff');
            Route::post('/expenses', 'ExpensesController@expenses');
            Route::post('/hasExpenses', 'ExpensesController@hasForDay');

            Route::post('/assignedAreas', 'MrSalesController@mrAssignedArea');
            Route::post('/assignedProducts', 'MrSalesController@mrAssignedProducts');
            Route::post('/salesDetails', 'MrSalesController@salesDetails');
            Route::post('/monthlySummery','MrSalesController@getMonthlySalesSummery');
            Route::post('/chemistWiseSale', 'MrSalesController@getChemistWiseSale');

            Route::post('/limitedChemistWiseSale', 'TestController@limitedChemistSale'); //testing purpose


            Route::post('/resetPassword', 'UserController@resetPassword');

            Route::post('search/otherCustomers', 'AutoSuggestController@searchOtherCustomers');
            Route::post('/missedCustomers', 'VisitsController@getMissedForDay');

            Route::post('/bataType/all','BataTypeController@getAll');

            // To be deleted after 1.0.9 released
            Route::post('/todayRoute','CurrentItineraryController@ItineraryForDay');

            Route::post('/monthlyItinerary','ItineraryTownServiceController@index');

            Route::post('/changeItinerary','ItineraryTownServiceController@changeItineraryByMr');
            Route::post('/approveItinerary','ItineraryTownServiceController@approveItinerary');

            Route::post('/notifications','NotificationController@sync');

            Route::post('/getDateDetails','ExpensesController@getDateDetails');

            // If only user has an itinerary

            Route::post('/bataType', 'BataTypeController@getToday');

            Route::post("/todayVisits","VisitsController@getAllForToday");
            Route::post("/lastFiveVisits", "VisitsController@getLastFiveVisits");
            Route::post("/lastFiveVisitDetails", "VisitsController@getLastFiveVisitDetails");
        });
    }
);
