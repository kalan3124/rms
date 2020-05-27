<?php
Route::get('order_vs_invoice','OrderVsInvoiceController@index');
Route::get('order_vs_invoice_search','OrderVsInvoiceController@getOrderVsInvoice');

Route::get('productivity_report','ProductivityController@getProductivity');

Route::get('unit_wise_target','UnitWiseTargetVsAchiController@index');
Route::get('unit_wise_target_search','UnitWiseTargetVsAchiController@getUnitWiseTarget');

Route::get('principal_wise_target','PrincipalWiseTargetVsAchiController@index');
Route::get('principal_wise_target_search','PrincipalWiseTargetVsAchiController@getPrincipalWiseTarget');

Route::get('town_cus_target','TownCustomerController@index');
Route::get('town_cus_target_search','TownCustomerController@getTownCusWiseTarget');

Route::get('exe_wise_sale','ExecutiveSaleController@index');
Route::get('exe_wise_sale_search','ExecutiveSaleController@getExeWiseSales');

Route::get('cus_wise_sale','CustomerWiseSaleController@index');
Route::get('cus_wise_sale_search','CustomerWiseSaleController@getCusWiseSales');

Route::get('ytd_cus_wise_sale','YtdCustomerWiseSaleController@index');
Route::get('ytd_cus_wise_sale_search','YtdCustomerWiseSaleController@getYtdCusWiseSales');

Route::get('day_analysis','DaySalesAnalysisReportController@index');
Route::get('day_analysis_search','DaySalesAnalysisReportController@getDaySales');
