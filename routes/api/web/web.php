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
    });
});
