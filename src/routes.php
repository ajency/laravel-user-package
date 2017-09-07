<?php

/*
|--------------------------------------------------------------------------
| Social Auth Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Social authentication
Route::group(['namespace' => 'AjUser'], function() {
	Route::get('/redirect/{provider}', 'SocialAuthController@redirect');
	Route::get('/callback/{provider}', 'SocialAuthController@callback');

	Route::group(['prefix' => 'api'], function () {
		Route::get('/login/{provider}', 'SocialAuthController@getDetails');
		Route::get('/logout/{provider}', 'SocialAuthController@logout');
	});
});