# Ajency Laravel User Authentication Package

- Version 1.0
- Updated on 07 Sept 2017

## Description
Contains Email Signup &amp; Social Auth, generating User Details (User Meta), User Communications Table &amp; Alter of Users Table with columns defined by user.

## Installation &amp; Configuration
1. Install Socialite by  <br/>
	> composer require laravel/socialite

2. Then in config/app.php, <br/>

	> 'providers' => [
    	// Other service providers...

    	Laravel\Socialite\SocialiteServiceProvider::class,
	],

	'aliases' => [
		...
		'Socialite' => Laravel\Socialite\Facades\Socialite::class,
	],

3. Create a folder '/packages/ajency/user/' under root Laravel project.
4. Clone this repo under the recently created folder.
5. In main project, open "composer.json", and then add <br/>
	> "Ajency\\User\\": "packages/ajency/user/src"<br/>
	
	under the defined 'key'
	
	"autoload": {
		...,

		"psr-4": {

			...

			"Ajency\\User\\": "packages/ajency/user/src"

		}
	}

6. In config/app.php, add <br/>
	'providers' => [

		...

		'Ajency\User\LaravelAjUserServiceProvider'

	],

	'aliases' => [

		...

		'AjUser' => 'Ajency\User\LaravelAjUserServiceProvider'
	]

7. Run
	> composer dump-autoload

8. Run 
	> php artisan vendor:publish

9. Open 'aj_user_migrations.php' file & add/edit the columns that the needed for your User flow.

10. After assigning the Column names Run <br/>
	> php artisan aj_user:migrate<br/>

This will generate the Models & migrations for new table & alter the old users table.

11. Then run <br/>
	> php artisan migrate<br/>

<b>Caution</b> : Laravel 5.4 has an issue with migrations regarding String length, please check this before running a migration on 5.4 version<br/>

12. Set your routes & other configurations in 'aj_user_config.php'.<br/>

13. Now update the 'config/services.php', with the following: <br/>
	
	return [
		...

		'<provider>' => [
	        'client_id' => env('<"PROVIDER">_ID'),
	        'client_secret' => env('<"PROVIDER">_SECRET'),
	        'redirect' => env('<"PROVIDER">_URL'),
	    ],
	]

    Example : 
	
	return [
		...

		'google' => [
	        'client_id' => env('GOOGLE_ID'),
	        'client_secret' => env('GOOGLE_SECRET'),
	        'redirect' => env('GOOGLE_URL'),
	    ],

	    'facebook' => [
	        'client_id' => env('FACEBOOK_ID'),
	        'client_secret' => env('FACEBOOK_SECRET'),
	        'redirect' => env('FACEBOOK_URL'),
	    ],
	    ...
	]

	then in .env define your Details like 

	GOOGLE_ID=xxxxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com<br/>
	GOOGLE_SECRET=XXXXXXXXXXXXXXXXXXXXXXX<br/>
	GOOGLE_URL=http://<domain_name>/callback/google<br/>

	FACEBOOK_ID=xxxxxxxxxxxxxxx<br/>
	FACEBOOK_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx<br/>
	FACEBOOK_URL=http://<domain_name>/callback/facebook<br/>

	Similar can be done for <b>twitter</b>, <b>linkedin</b>, <b>github</b> or <b>bitbucket</b>.

14. Update the routes/web.php with this <br/>

	Route::group(['namespace' => 'Ajency'], function() {

		Route::get('/redirect/{provider}', 'User\SocialAuthController@urlSocialAuthRedirect');
		Route::get('/callback/{provider}', 'User\SocialAuthController@urlSocialAuthCallback');

		Route::group(['prefix' => 'api'], function () {
			Route::get('/login/{provider}', 'User\SocialAuthController@apiSocialAuth');
			Route::get('/logout/{provider}', 'User\SocialAuthController@logout');
		});
	});
	<b>Note:</b> This will be <b>updated later</b> with Facade, so that user can directly access as
		> AjUser::routes();

	For now, do not use <b>AjUser::routes()</b> in routes/web.php

15. 