<?php
Route::post('/login','UserController@login')->middleware('guest');


Route::post('/upload/{type}', 'UploadController@save');

Route::group(['middleware'=>'auth:api'],function(){
    Route::group(['middleware'=>'log'], function () {

        // MEDICAL

        Route::post('/sidebar','SidebarController@getAll');
        Route::post('/sidebar/main','SidebarController@getMain');
        Route::post('/user','UserController@getUser');
        Route::post('/user_change','UserController@updateUserDetails');
        Route::post('/user_other','UserController@loadOtherDetails');
        Route::post('/user_other_new','UserController@otherDetails');
        Route::post('/panel/fm_team_member/dropdown','ItineraryController@searchTeamMembers');
        Route::post('/panel/team_member/dropdown','Reports\ExpenceStatementReportController@searchTeamMembers');
        Route::post('/panel/team_member_with_itinerary/dropdown','ItineraryController@searchTeamMembersWithItinerary');
        Route::post('/panel/area_user_itinerary/dropdown','ItineraryController@searchUserByArea');

        Route::group(['prefix'=>'/panel/{form}'],function(){

            Route::post('/info','FormController@getInformations');
            Route::post('/search','FormController@search');
            Route::post('/dropdown','FormController@dropdownSearch');
            Route::post('/{mode}','FormController@submit')->where('mode','create|update');
            Route::post('/delete','FormController@delete');
            Route::post('/restore','FormController@restore');
            Route::post('/csv','FormController@saveCSV');
            Route::post('/pdf','FormController@savePDF');
            Route::post('/xlsx','FormController@saveXLSX');

        });

        Route::group(['prefix' => 'itinerary'], function () {

            Route::post('/load','ItineraryController@load');
            Route::post('/dayTypes','ItineraryController@dayTypes');
            Route::post('/save','ItineraryController@saveToDB');

            Route::group(['prefix' => 'approval'], function () {

                Route::post('/search','ItineraryApprovalController@search');
                Route::post('/approve','ItineraryApprovalController@approve');

            });

        });

        Route::group(['prefix' => 'doc_time_table'], function () {

            Route::post('/load','DoctorTimeTableController@load');
            Route::post('/save','DoctorTimeTableController@save');

        });

        Route::group(['prefix' => 'product_allocations'], function () {

            Route::post('/load','ProductAllocationController@load');
            Route::post('/save','ProductAllocationController@save');

        });

        Route::group([
            'prefix' => 'report',
            'namespace'=>'Reports'
        ], function () {
            require_once('reports.php');
        });

        Route::post('/gps/search','GPSController@search');

        Route::group(['prefix' => 'standard_itinerary'], function () {

        Route::post('save','StandardItineraryController@save');
        Route::post('load','StandardItineraryController@load');

        });

        Route::group(['prefix' => 'user_area'], function () {

            Route::post('levels','UserAreaController@getTerritoryLevels');
            Route::post('load','UserAreaController@getDetailsForUser');
            Route::post('create','UserAreaController@create');
            Route::post('remove','UserAreaController@remove');
            Route::post('remove_all','UserAreaController@removeAll');

        });

        Route::group(['prefix' => 'user_customer'], function () {

            Route::post('load','UserCustomerController@loadByUser');
            Route::post('create','UserCustomerController@create');
            Route::post('remove','UserCustomerController@remove');
            Route::post('remove_all','UserCustomerController@removeAll');

        });

        Route::group(['prefix' => 'permissions'], function () {

            Route::post('load','PermissionController@loadItems');
            Route::post('save','PermissionController@save');
            Route::post('loadByUser','PermissionController@loadByUser');

        });

        Route::group(['prefix' => 'team_products'], function () {

            Route::post('save','TeamProductController@save');
            Route::post('load','TeamProductController@load');
            Route::post('/load_product_by_Principal','TeamProductController@loadProductByPrincipal');

        });

        Route::group(['prefix' => 'upload_csv'], function () {
            Route::post("info","UploadCSVController@info");
            Route::post('format','UploadCSVController@downloadFormat');
            Route::post('submit','UploadCSVController@uploadFile');
            Route::post('status','UploadCSVController@checkStatus');
        });

        Route::group(['prefix' => 'issue'], function () {
            Route::post("create","IssueController@create");
            Route::post("search","IssueController@search");
        });

        Route::group(['prefix'=>'target'],function(){
            Route::post('load','UserTargetController@load');
            Route::post('save','UserTargetController@save');
        });

        Route::group(['prefix'=>'doc_approve'],function(){
            Route::post('search','DoctorApproveController@search');
            Route::post('save','DoctorApproveController@save');
            Route::post('delete','DoctorApproveController@delete');
        });

        Route::group(['prefix'=>'itinerary_viewer'],function(){
            Route::post('search','ItineraryViewerController@search');
            Route::post('load','ItineraryViewerController@loadItinerary');
        });

        Route::group(['prefix'=>'itinerary_change'],function(){

            Route::post('/search','ItineraryChangesController@search');

        });

        Route::group(['prefix'=>'user_clone'],function(){
            Route::post('sections','UserCloneController@getItems');
            Route::post('save','UserCloneController@cloneUser');
        });

        Route::group(['prefix' => 'doctor_town'], function () {
            Route::post('get_towns_by_doctor','DoctorTownController@getTownsByDoctor');
            Route::post('save','DoctorTownController@save');
        });

        Route::group(['prefix'=>'sales_allocation'],function(){
            Route::post('search/towns','SalesAllocationController@searchTowns');
            Route::post('search/products','SalesAllocationController@searchProducts');
            Route::post('search/customers','SalesAllocationController@searchCustomers');
            Route::post('load','SalesAllocationController@fetchData');
            Route::post('save','SalesAllocationController@save');
        });

        Route::group(['prefix'=>'invoice_allocation'],function(){
            Route::post('load','InvoiceAllocationController@loadData');
            Route::post('search','InvoiceAllocationController@search');
            Route::post('save','InvoiceAllocationController@save');
            Route::post('search_product','InvoiceAllocationController@searchProducts');
        });

        Route::group(['prefix'=> 'hod_gps_tracking'],function(){
            Route::post('/load', 'HodGpsTrackingController@search');
        });

        // SALES ROUTES
        Route::group(['prefix' => 'route_chemist/sales'], function () {
            Route::post('/load','Sales\RouteChemistController@load');
            Route::post('/save','Sales\RouteChemistController@save');
            Route::post('/load_routes_by_area','Sales\RouteChemistController@loadRoutesByArea');
            Route::post('/load_chemits_by_area','Sales\RouteChemistController@loadChemitsByArea');
        });

        Route::group(['prefix'=>'sales_itinerary'],function (){
            Route::post('/load','Sales\ItineraryController@load');
            Route::post('/save','Sales\ItineraryController@save');
        });

        Route::group(['prefix'=>'sales_itinerary_approval'],function (){
            Route::post('/search','Sales\SalesitineraryApprovalController@search');
            Route::post('/approve','Sales\SalesitineraryApprovalController@approve');
        });

        Route::group(['prefix'=>'town_wise_sales'],function (){
            Route::post('/search','Reports\Sales\TownWiseSalesController@search');
            Route::post('/excel','Reports\Sales\TownWiseSalesController@printExcel');
        });

        Route::post('/sales_gps/search','Sales\GPSController@search');

        Route::group(['prefix'=>'sr_target'],function(){
            Route::post('load','Sales\UserTargetController@load');
            Route::post('save','Sales\UserTargetController@save');
        });

        Route::group(['prefix'=>'sales_weekly_target'],function (){
            Route::post('/search','Sales\WeeklyTargetAllocationController@search');
            Route::post('/save','Sales\WeeklyTargetAllocationController@saveTargets');
        });

        Route::group(['prefix'=>'expenses_edit'],function (){
            Route::post('/search','Sales\ExpensesEditController@search');
            Route::post('/save','Sales\ExpensesEditController@saveEditWExpenses');
            Route::post('/asm_save','Sales\ExpensesEditController@saveAsmExp');
            Route::post('/user_roll','Sales\ExpensesEditController@getUser');
        });

        Route::group(['prefix'=>'dc_allocation'],function (){
            Route::post('/load','Sales\DCAllocationController@load');
            Route::post('/save','Sales\DCAllocationController@save');
        });

        Route::group(['prefix'=>'dc_sales_itinerary'],function (){
            Route::post('/load','Sales\DCItineraryController@load');
            Route::post('/save','Sales\DCItineraryController@save');
        });

        // DISTRIBUTOR ROUTES
        Route::group(['prefix'=>'sr_customer'],function(){
            Route::post('/load','Distributor\UserCustomerController@loadCustomers');
            Route::post('/save','Distributor\UserCustomerController@save');
        });

        Route::group(['prefix' => 'route_chemist/distributor'], function () {
            Route::post('/load','Distributor\RouteCustomerController@load');
            Route::post('/save','Distributor\RouteCustomerController@save');
            Route::post('/load_routes_by_area','Distributor\RouteCustomerController@loadRoutesByArea');
            Route::post('/load_chemits_by_area','Distributor\RouteCustomerController@loadChemitsByArea');
        });

        Route::group(['prefix'=> 'purchase_order'],function(){
            Route::post('/getNumber','Distributor\PurchaseOrderController@getNumber');
            Route::post('/getDetails', 'Distributor\PurchaseOrderController@getDetails');
            Route::post('/save', 'Distributor\PurchaseOrderController@save');
        });

        Route::group(['prefix'=> 'dsr_allocation'],function(){
            Route::post('/save', 'Distributor\DsrAllocationController@save');
            Route::post('/load', 'Distributor\DsrAllocationController@load');
        });

        Route::group(['prefix'=> 'site_allocation'],function(){
            Route::post('/load_site', 'Distributor\SiteAllocationController@loadSite');
            Route::post('/save', 'Distributor\SiteAllocationController@save');
            Route::post('/load', 'Distributor\SiteAllocationController@load');
        });

        Route::group(['prefix'=>'sr_product'],function(){
            Route::post('/load','Distributor\UserProductController@loadProducts');
            Route::post('/save','Distributor\UserProductController@save');
        });

        Route::group(['prefix'=>'create_invoice'],function(){
            Route::post('/load','Distributor\CreateInvoiceController@load');
            Route::post('/save','Distributor\CreateInvoiceController@save');
            Route::post('/load_bonus_scheme','Distributor\CreateInvoiceController@loadBonus');
            Route::post('/load_batch_details','Distributor\CreateInvoiceController@loadBatchDetails');
        });

        Route::group(['prefix'=> 'salesman_allocation'],function(){
            Route::post('/save', 'Distributor\SalesmanAllocationController@save');
            Route::post('/load', 'Distributor\SalesmanAllocationController@load');
        });

        Route::group(['prefix'=> 'stock_adjusment'],function(){
            Route::post('/loadAdjNo', 'Distributor\StockAdjusmentController@loadAdjNo');
            Route::post('/load_data', 'Distributor\StockAdjusmentController@loadData');
            Route::post('/save_data', 'Distributor\StockAdjusmentController@saveData');
        });

        Route::group(['prefix'=>'direct_invoice'],function(){
            Route::post('load_next_number','Distributor\DirectInvoiceController@getNextInvoiceNumber');
            Route::post('load_line_info','Distributor\DirectInvoiceController@loadLineInfo');
            Route::post('save','Distributor\DirectInvoiceController@save');
            Route::post('load_bonus_scheme','Distributor\DirectInvoiceController@getBonus');
            Route::post('/load_batch_details','Distributor\DirectInvoiceController@loadBatchDetails');
        });

        Route::group(['prefix'=>'create_return'],function(){
            Route::post('load_next_number','Distributor\CreateReturnController@getNextReturnNumber');
            Route::post('load_line_info','Distributor\CreateReturnController@loadLineInfo');
            Route::post('save','Distributor\CreateReturnController@save');
            Route::post('load_bonus_scheme','Distributor\CreateReturnController@getBonus');
        });

        Route::group(['prefix'=>'grn_confirm'],function(){
            Route::post('fetch','Distributor\GRNConfirmController@loadInformations');
            Route::post('save','Distributor\GRNConfirmController@save');
        });

        Route::group(['prefix' => 'sales_process'], function () {
            Route::post('start','Distributor\SalesProcessController@submit');
            Route::post('checkProgress','Distributor\SalesProcessController@checkProgress');
        });

        Route::group(['prefix'=>'order_based_return'],function(){
            Route::post('load_info','Distributor\OrderBasedReturnController@getInvoiceInfo');
            Route::post('load_bonus','Distributor\OrderBasedReturnController@getBonus');
            Route::post('save','Distributor\OrderBasedReturnController@save');
        });

        Route::group(['prefix'=> 'purchase_order_confirm'],function(){
            Route::post('/load', 'Distributor\PurchaseOrderConfirmController@load');
            Route::post('/getDetails', 'Distributor\PurchaseOrderConfirmController@getDetails');
            Route::post('/save', 'Distributor\PurchaseOrderConfirmController@save');
        });

        Route::group(['prefix'=> 'invoice_payment'],function(){
            Route::post('/load', 'Distributor\InvoicePaymentController@load');
            Route::post('/save', 'Distributor\InvoicePaymentController@save');
        });

        // User Team Allocation
        Route::group(['prefix'=>'user_team'],function(){
            Route::post('/load','UserTeamController@load');
            Route::post('/save','UserTeamController@save');
        });

        Route::group(['prefix'=>'company_return'],function(){
            Route::post('/load','Distributor\CompanyReturnController@load');
            Route::post('/save','Distributor\CompanyReturnController@save');
        });

        Route::group(['prefix'=>'competitor'],function(){
            Route::post('/load','Sales\CompetitorsController@load');
            Route::post('/loadEdit','Sales\CompetitorsController@loadEdit');
            Route::post('/edit','Sales\CompetitorsController@dateEdit');
        });
    });
});
