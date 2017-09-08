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
Route::group(['namespace' => 'Ajency'], function() {
	Route::get('/redirect/{provider}', 'User\SocialAuthController@urlSocialAuthRedirect');
	Route::get('/callback/{provider}', 'User\SocialAuthController@urlSocialAuthCallback');

	Route::group(['prefix' => 'api'], function () {
		Route::get('/login/{provider}', 'User\SocialAuthController@apiSocialAuth');
		Route::get('/logout/{provider}', 'User\SocialAuthController@logout');
	});
});