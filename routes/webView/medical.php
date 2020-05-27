<?php
Route::get('getTrends','TrendController@getTrends');

Route::get('targetVsAchievement', 'TargetVsAchieveController@index');
Route::get('targetVsAchievementSearch', 'TargetVsAchieveController@search');

Route::get('itineraryForDay', 'ItineraryForDayController@index');

Route::get('missedVisitDetails', 'MissedVisitController@index');
Route::get('missedVisitSearch', 'MissedVisitController@search');

Route::get('monthlyItinerary', 'itinerarytownController@index');

Route::get('dayWiseDoctorVisit', 'dateWiseDoctorVisitController@index');

Route::get('brandWiseTarget','BrandWiseTargetVsAchievementController@index');
Route::get('brandWiseTargetSearch','BrandWiseTargetVsAchievementController@search');
