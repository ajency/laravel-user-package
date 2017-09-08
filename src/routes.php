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
Route::group(['namespace' => 'Ajency\User'], function() {
	Route::get('/redirect/{provider}', 'SocialAuthController@urlSocialAuthRedirect');
	Route::get('/callback/{provider}', 'SocialAuthController@urlSocialAuthCallback');

	Route::group(['prefix' => 'api'], function () {
		Route::get('/login/{provider}', 'SocialAuthController@apiSocialAuth');
		Route::get('/logout/{provider}', 'SocialAuthController@logout');
	});
});