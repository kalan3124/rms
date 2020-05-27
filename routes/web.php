<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
Route::get('test/oracle', 'TestController@oracleTest');
Route::get('test/subTowns', 'TestController@subTowns');
Route::get('test/salesQuery', 'TestController@salesQuery');
Route::get('test/products', 'TestController@products');
Route::get('test/emailViews', 'TestController@emailViews');
Route::get('integrateInvoiceheaders', 'TestController@getInvoiceHeaders');
Route::get('integrateInvLines', 'TestController@getInvLines');

Route::group(['prefix' => '/webView'], function () {

    Route::group([
        'prefix' => '/medical',
        'namespace' => 'WebView\Medical',
        'middleware' => 'auth.report',
    ], function () {
        require_once ('webView/medical.php');
    });

    Route::group([
        'prefix' => '/sales',
        'namespace' => 'WebView\Sales',
        'middleware' => 'auth.report',
    ], function () {
        require_once ('webView/sales.php');
    });

    Route::group([
        'prefix' => '/distributor',
        'namespace' => 'WebView\Distributor',
        'middleware' => 'auth.report',
    ], function () {
        require_once ('webView/distributor.php');
    });

});

// Invoice Printing
Route::get('test/invoice', 'Web\Reports\Distributor\DistributorInvoiceReportController@print');

Auth::routes();

Route::get('/test/{form}', 'FormController@savePDF');

Route::view('/{path?}', 'react')
    ->where('path', '.*')
    ->name('react');

// Route::group(['prefix' => 'mr'], function () {
// Route::get('/getTrends','TrendController@getTrends');
// });
