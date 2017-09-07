# Ajency Laravel User Authentication Package

- Version 1.0
- Updated on 07 Sept 2017

## Description
Contains Email Signup &amp; Social Auth, generating User Details (User Meta), User Communications Table &amp; Alter of Users Table with columns defined by user.

## Installation
1. Create a folder '/packages/ajency/user/' under root Laravel project.
2. Clone this repo under the recently created folder.
3. In main project, open "composer.json", and then add <br/>
	> "Ajency\\User\\": "packages/ajency/user/src"<br/>
	
	under the defined 'key'
	
	"autoload": {
		...,

		"psr-4": {

			...

			"Ajency\\User\\": "packages/ajency/user/src"

		}
	}

4. In config/app.php, add <br/>
	'providers' => [

		...

		'Ajency\User\LaravelAjUserServiceProvider'

	],

	'aliases' => [

		...

		'AjUser' => 'Ajency\User\LaravelAjUserServiceProvider'
	]

5. Run
	> composer dump-autoload

6. Run 
	> php artisan vendor:publish

7. Open 'aj_user_migrations.php' file & add/edit the columns that the needed for your User flow.

8. After assigning the Column names Run <br/>
	> php artisan aj_user:migrate<br/>

This will generate the Models & migrations for new table & alter the old users table.

9. Then run <br/>
	> php artisan migrate<br/>

<b>Caution</b> : Laravel 5.4 has an issue with migrations regarding String length, please check this before running a migration on 5.4 version<br/>

10. Set your routes & other configurations in 'aj_user_config.php'.<br/>

11. Now update the 'config/services.php', with the following: <br/>
	
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

	GOOGLE_ID=xxxxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com
	GOOGLE_SECRET=XXXXXXXXXXXXXXXXXXXXXXX
	GOOGLE_URL=http://<domain_name>/callback/google

	FACEBOOK_ID=xxxxxxxxxxxxxxx
	FACEBOOK_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	FACEBOOK_URL=http://<domain_name>/callback/facebook