<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
    
Route::group([
    'prefix' => '/web',
    'namespace'=>'Web'
], function () {
    require_once('api/web/web.php');
});

Route::group(['prefix' => '/medical'], function () {

    Route::group([
        'prefix'=>'/v1',
        'namespace'=>'API\Medical\V1'
    ],function(){
        require_once('api/medical/v1.php');
    });

});

Route::group(['prefix' => '/sales'], function () {

    Route::group([
        'prefix'=>'/v1',
        'namespace'=>'API\Sales\V1'
    ],function(){
        require_once('api/sales/v1.php');
    });

});


Route::group(['prefix' => '/distributor'], function () {

    Route::group([
        'prefix'=>'/v1',
        'namespace'=>'API\Distributor\V1'
    ],function(){
        require_once('api/distributor/v1.php');
    });

});
