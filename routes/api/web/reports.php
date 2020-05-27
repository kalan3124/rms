<?php

function simpleReportRoute($reportName,$controllerName){
    Route::group(['prefix' => $reportName], function ()use($controllerName) {
        Route::post('search',$controllerName.'@getResults');
        Route::post('info',$controllerName.'@info');
        Route::post('pdf',$controllerName.'@savePDF');
        Route::post('csv',$controllerName.'@saveCSV');
        Route::post('xlsx',$controllerName.'@saveXlsx');
    });
}

simpleReportRoute('activity_log','ActivityLogReportController');
simpleReportRoute('rep_attendance','AttendanceController');
simpleReportRoute('unproductive','UnproductiveController');
simpleReportRoute('productive','ProductiveController');
simpleReportRoute('promotion','PromotionReportController');
simpleReportRoute('itinerary_plan','ItineraryPlanReportController');
simpleReportRoute('jfw_summary','JFWSummaryReportController');
simpleReportRoute('town_sales','TownSalesReport');
simpleReportRoute('product_sales','ProductSalesReport');
simpleReportRoute('exp_statement_sum','ExpenceStatementSummeryReportController');
simpleReportRoute('ytd_expences','YTDExpencesReportController');
simpleReportRoute('target_vs_achievement','TargetVsAchievementReportController');
simpleReportRoute('target_vs_achievement_new','TargetVsAchievementReportControllerNew');
simpleReportRoute('ytd_product','YtdProductReportController');
simpleReportRoute('chemist_sales','ChemistSalesReport');
simpleReportRoute('mr_customer','MrCustomerReportController');
simpleReportRoute('mr_principal','MrPrincipalReportController');
simpleReportRoute('mr_principal_new','MrPrincipalReportControllerNew');
simpleReportRoute('mr_principal_alt','MrPrincipalAltReportController');
simpleReportRoute('dcr_execute','DcrExecuteSummaryReportController');
simpleReportRoute('mileage_report','MilageReportController');
simpleReportRoute('mr_ps_itinerary','MRPSItineraryController');
simpleReportRoute('ytd_sales_sheet_report','YtdMonthlySalesSheetController');
simpleReportRoute('mr_target_achiv','MrtargetAchivementReportController');
simpleReportRoute('dcr_summary','DcrSummaryReportController');
simpleReportRoute('trend_report','TrendReportController');
simpleReportRoute('ytd_product_report','YtdProductController');
simpleReportRoute('ytd_target_report','YtdTargetvsAchiReportController');
simpleReportRoute('ytd_expenses_report','YtdExpnesesReportController');
simpleReportRoute('promotion_report','PromotionPlanReportController');
simpleReportRoute('mr_target_report','ViewAllMonthTargetReportController');
simpleReportRoute('monthly_report','MonthlyReportController');
simpleReportRoute('promotinal_report','PromotionalPlanReportController');
simpleReportRoute('monthly_expenses_report','MonthlyExepensesReportController');
simpleReportRoute('visits_for_day','TodayVisitsReportController');
simpleReportRoute('gps_status','GPSStatusChangesReportController');
simpleReportRoute('day_wise_mr_achievement_report','DayWiseMrWiseAchievementReportController');

// Allocation Reports
simpleReportRoute('mr_chemists_alloc','Allocations\MRChemistReportController');
simpleReportRoute('mr_doctor_alloc','Allocations\MRDoctorReportController');
simpleReportRoute('mr_territory_alloc','Allocations\MRSubTownReportController');
simpleReportRoute('mr_product_alloc','Allocations\MRProductReportController');
simpleReportRoute('sales_allocation','Allocations\SalesAllocationReportController');
Route::post('sales_allocation/delete','Allocations\SalesAllocationReportController@delete');
simpleReportRoute("invoice_allocation","Allocations\InvoiceAllocationReportController");
Route::post('invoice_allocation/delete','Allocations\InvoiceAllocationReportController@delete');

// Sales
simpleReportRoute('sr_allocated_chemist','Sales\SRAllocatedCustomerController');
simpleReportRoute('sr_allocated_product','Sales\SRAllocatedProductController');
simpleReportRoute('sales_order','Sales\SalesOrderReportController');
simpleReportRoute('sfa_unproductive','Sales\UnproductiveController');
simpleReportRoute('return_note','Sales\ReturnNoteController');

// Sales Reports
simpleReportRoute('unit_wise_target','Sales\UnitWiseTargetVsAchivReportController');
simpleReportRoute('customer_count_report','Sales\CustomerCountReportController');
simpleReportRoute('ytd_unit_report','Sales\YtdUnitWiseSlaeReportController');
simpleReportRoute('town_wise_report','Sales\TownWiseTargetVsAchivReportController');
simpleReportRoute('order_vs_report','Sales\OrderVsInvoiceReportController');
simpleReportRoute('productivity_report','Sales\ProductivityReportController');
simpleReportRoute('jfw_report','Sales\JfwReportController');
simpleReportRoute('route_wise_report','Sales\RouteWiseTargetVsAchReportController');
simpleReportRoute('customer_sales_report','Sales\YtdCustomerWiseSalesReportController');
simpleReportRoute('principal_wise_report','Sales\PrincipalWiseTargetVsAchiReportController');
simpleReportRoute('attendance_report','Sales\AttendanceController');
simpleReportRoute('daily_analysis_report','Sales\DaySalesAnalysisReportController');
simpleReportRoute('customer_allo_report','Sales\CustomerTargetAllocationReport');
simpleReportRoute('login_details','Sales\UserLoginDetailsReportController');
simpleReportRoute('gps_report','Sales\GpsReportController');
simpleReportRoute('cus_achi_report','Sales\CustomerTargetVsAchiReportController');
simpleReportRoute('cus_ytd_achi_report','Sales\YtdCustomerTargetVsAchiReportController');
simpleReportRoute('town_wise_sales','Sales\TownWiseSalesController');
simpleReportRoute('ex_wise_sales','Sales\ExecutiveSaleReportController');
simpleReportRoute('exp_sales','Sales\ExpensesSaleStatementReportController');
simpleReportRoute('exp_sales_summury','Sales\ExpensesSalesStatementSummuryReportController');
simpleReportRoute('sr_productivity_report','Sales\SrWiseDailyProductivityReportController');
simpleReportRoute('sr_original_report','Sales\SrTargetVsAchivementReportController');
simpleReportRoute('sr_area_wise_report','Sales\SrAreaWiseUnitTargetVsAchi');
simpleReportRoute('sr_inc_report','Sales\SrWeeklyIncentiveReportController');

simpleReportRoute('price_list','Sales\PriceListController');
simpleReportRoute('route_chemist','Sales\RouteCustomerController');
simpleReportRoute('sr_chemist','Sales\SrChemistController');

simpleReportRoute('expenses_statement','ExpenceStatementReportController');

Route::group(['prefix' =>'exp_statement'], function () {
    Route::post('search','ExpenceStatementReportController@searchReport');
    Route::post('types','ExpenceStatementReportController@getTypesAndReasons');
});

Route::group(['prefix' =>'fm_level_report'], function () {
    Route::post('search','FmLevelReportController@search');
});

Route::group(['prefix' =>'mr_ps_report'], function () {
    Route::post('search','MrPsItineraryReportController@search');
});

Route::group(['prefix' =>'ytd_sales_sheet_report'], function () {
    Route::post('search','YtdMonthlySalesSheetController@search');
});

Route::group(['prefix' =>'team_performance'], function () {
    Route::post('search','TeamPerformanceReportController@search');
});


simpleReportRoute('coverage_summary', 'CoverageSummaryReportController');
simpleReportRoute('doctor_coverage', 'DoctorCoverageReportController');
simpleReportRoute('chemist_coverage', 'ChemistCoverageReportController');

simpleReportRoute('app_usage', 'AppUsageController');
simpleReportRoute('exp_bill', 'ExpensesBillReportController');

simpleReportRoute('itinerary_change', 'ItineraryChangesController');
Route::post('itinerary_change/approve','ItineraryChangesController@approve');


// Distributor Reports
simpleReportRoute('dist_sales_order','Distributor\SalesOrderReportController');
simpleReportRoute('purchase_order','Distributor\PurchaseOrderReportController');
simpleReportRoute('dsr_wise_sale','Distributor\DsrWiseSalesReportController');
simpleReportRoute('agency_wise_sale','Distributor\AgencyWiseSaleReportController');
simpleReportRoute('bouns_report','Distributor\BounsReimbursementReportController');
simpleReportRoute('stock_ajust_report','Distributor\StockAjustmentReportController');
simpleReportRoute('pro_wise_report','Distributor\ProductWiseSaleReportController');
simpleReportRoute('cus_wise_report','Distributor\CustomerWiseReportController');
simpleReportRoute('primary_order_report','Distributor\PrimaryOrderVsInvoiceReportController');
simpleReportRoute('rd_order_report','Distributor\RDOrderVsInvoiceReportController');
simpleReportRoute('dsr_dis_allo_report','Distributor\Allocations\DsrDistributorAllocationReportController');
simpleReportRoute('dsr_cus_allo_report','Distributor\Allocations\DsrCustomerAllocationReportController');
simpleReportRoute('route_cus_allo_report','Distributor\Allocations\RouteCustomerAllocationReportController');
simpleReportRoute('site_dis_allo_report','Distributor\Allocations\SiteDistributorAllocationReportController');
simpleReportRoute('dsr_pro_allo_report','Distributor\Allocations\DsrProductAllocationReportController');
simpleReportRoute('sr_allo_report','Distributor\Allocations\SrAllocationReportController');
simpleReportRoute('stock_move_report','Distributor\StockMovementReportController');
simpleReportRoute('stock_adjustment_report','Distributor\StocksAdjustmentReportController');
simpleReportRoute('rd_sales','Distributor\DistributorRdSalesReportController');
// Route::post('adjustment/print','Distributor\StocksAdjustmentReportController@print');
simpleReportRoute('stock_writeoff_report','Distributor\StockWriteOffReportController');
// Route::post('writeoff/print','Distributor\StockWriteOffReportController@print');
simpleReportRoute('bonus_approval_report','Distributor\BonusApprovalReportController');
simpleReportRoute('dsr_wise_stock','Distributor\DistributorWiseStockReportController');
simpleReportRoute('dsr_customer_wise_sales','Distributor\CustomerWiseSalesReportController');
simpleReportRoute('dsr_product_wise_sales','Distributor\ProductWiseSalesReportController');
Route::post('bonus_approval_report/approve','Distributor\BonusApprovalReportController@approve');
simpleReportRoute('dsr_productivity_report','Distributor\SrWiseDailyProductivityReportController');
simpleReportRoute('new_return_report','Distributor\NewReturnReportController');



simpleReportRoute('good_received_note','Distributor\GoodReceivedNoteReportController');
Route::post('good_received_note/print','Distributor\GoodReceivedNoteReportController@print');

simpleReportRoute('invoice','Distributor\DistributorInvoiceReportController');
Route::post('invoice/print','Distributor\DistributorInvoiceReportController@print');

simpleReportRoute('return_report','Distributor\ReturnReportController');
Route::post('return/print','Distributor\ReturnReportController@print');

simpleReportRoute('stock_statement','Distributor\StockStatementReportController');

simpleReportRoute('company_return','Distributor\CompanyReturnReportController');
Route::post('company_return/confirm','Distributor\CompanyReturnReportController@confirm');
Route::post('company_return/print','Distributor\CompanyReturnReportController@print');

simpleReportRoute('payment','Distributor\DistributorPaymentReportController');
Route::post('payment/print','Distributor\DistributorPaymentReportController@print');

simpleReportRoute('dcr_profile','DoctorProfileReportController');
simpleReportRoute('list_credit','Distributor\ListCreditReportController');
