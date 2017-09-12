# Ajency Laravel User Authentication Package

- Version 1.0
- Updated on 12 Sept 2017

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
	
	under "psr-4"
	
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

9. Open 'aj_user_migrations.php' file & add/edit the columns that are needed for your User flow.
	Here are few configurations available

	Under Table level,

	[ <br/>

        "model" -> Model Name & also Migration file is generated & Table Name generated relating to the Model Name
    
        "table" -> Table name to be assigned for Migrations (Note: Only Migration file is generated)
    
        "status" -> (create / alter)
    
        "columns" -> [Array of Columns]
    
            "columns" -> array( 
    
                "column" => "<column_name>", 
    
                "type" => "<column_type>", // Available column Types are ["string", "text", "boolean", "integer", "decimal", "float", "date", "datetime", "timestamp", "increments"]
    
                "size" => "size of the Column - < Only for String Type >",
    
                "digit" => "Digits to display - < Only for Float Type >", "decimal_pt" => "numbers to store after decimal point - < Only for Float Type >",
    
                "precision" => "Digits of precision to store < Only for Decimal Type >", "scale" => "Decimal point scale < Only for Decimal Type >",
    
                "comment" => "<comment_for_the_column>", 
    
                "nullable" => "<true/false> [Decides whether Column is nullable or not]", 
    
                "default" => "<default_value> [Sets default value on SAVE]"
    
            )
    ] <br/>

    For ex:
	
	[<br/>

        array(

            "table" => "<table_A> [this field is only used if status == 'alter' or (status == 'create' & 'model' is not defined)]",
            
            "model" => "ModelA [this field is only used if status == 'create']", // On defining it, will Migration files along with the Model file in Application Level

            "status" => "< create / alter >",
            
            "columns" => [
            
                array("column" => "<Col-1>", "type" => "string", "size" => 100, "nullable" => true, "comment" => "Internal / Registered (has password) / Guest (no password)"),
            
                array("column" => "<Col-2>", "type" => "boolean", "default" => 0),
            
                array("column" => "<Col-3>", "type" => "datetime", "nullable" => true),
            
                array("column" => "<Col-4>", "type" => "date", "nullable" => true),
            
                array("column" => "<Col-5>", "type" => "timestamp", "nullable" => true),
            
                array("column" => "<Col-6>", "type" => "integer", "nullable" => true),
            
                array("column" => "<Col-7>", "type" => "float", "digit" => <Digit limit>, "decimal_pt" => <Decimal_pt_limit>, "nullable" => true),
            
                array("column" => "<Col-8>", "type" => "decimal", "precision" => <Precision limit>, "scale" => <decimal scale limit>, "nullable" => true),
            
                array("column" => "<Col-9>", "type" => "increments", "nullable" => true),
            
            ]
        
        ),

        ...

	]

10. After <b>assigning</b> the column names, run <br/>
	> php artisan aj_user:migrate<br/>

This will generate the Models & migrations for new table & alter the old users table.

11. Then run <br/>
	> php artisan migrate<br/>

<b>Caution</b> : Laravel 5.4 has an issue with migrations regarding String length, please check this before running a migration on 5.4 version<br/>

12. Set your routes & other configurations in 'aj_user_config.php'.<br/>
	Possible options available are: <br/>
	[

		"social_failure_redirect_url" => "/",
		
		"social" => ["google", "facebook"],
		
		"social_email_domain" => "aj",
		
		"table_required_fields" => [
		
			array("table" => "users", "columns" => ["type", "status", "signup_source"]),
		
			array("table" => "user_details", "columns" => ["area", "city"]),
		
			array("table" => "user_communications", "columns" => [])
		
		]
	]

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
	<br/><b>Note:</b> This will be <b>updated later</b> with Facade, so that user can directly access as <br/>
	> AjUser::routes();

	For now, <b>do not use</b> AjUser::routes() in routes/web.php

## Package Functions that are available at your disposal
1. Functions available:
	- getSocialData(<user_data>, $provider); // $provider -> ['email_signup', 'google', 'facebook', .....]
	- validateUserLogin($social_data["user"], $provider);
	- updateOrCreateUser(<user_data_json>, <user_detail_data_json>, <user_comm_data_json>)
	- getUserData(<user_data_json>, is_id=<true/false>)
	- updateOrCreateUserComm(<user_object - DB object>, <user_comm_data_json>) // Create or Update the UserCommunication Table
	- updateOrCreateUserDetails(<user_object - DB object>, <'user_details_data_json'>, search_by_column='user_id column', search_by_column_value='<integer / string value>') // Create or Update UserDetail Table

2. Accessing the above functions: <br/>
Define <br/>
> use Ajency\User\Ajency\socialaccount\SocialAccountService;
> use Ajency\User\Ajency\userauth\UserAuth;

on the header of the file. Then, <br/>
> $service = new SocialAccountService;
> $userauth_obj = new UserAuth;

Now, you can access the functions by,
### SocialAccountService functions:
> $social_data = $service->getSocialData($account, $provider);

### UserAuth functions:
> $user_data = $user_obj;
> $userauth_obj->getUserData($user_data, false); // <parameter 1> -> User Object, <parameter 2> -> "is_id = false"
Response: ["user" => <User_Object>, "user_details" => <UserDetail_Object>, "user_comm" => <UserCommunication_object>, "status" => "< true / false>", "message" => "...."]


$user_data = array("username" => "12345@ajgoogle.com", "password" => "hash password value", "name" => "xxxxx xxxxxxxxx", "provider" => "<'google' / 'facebook'>");<br/>
$user_detail =["user_id" => $user->id];<br/>
$user_comm = ["email" => "xxxxxx@xxxxxxx.com", "contact" => "+xxxxxxxxxxxxx", "contact_type" => "<'mobile' / 'telephone'>", "object_type" => "<'db' related to>", "object_id" => <'object'>->id]; <br/>
> $userauth_obj->updateOrCreateUser($user_data, "", $user_comm);
Response: array("user" => $user, "user_details" => isset($detail_response["data"]) ? $detail_response["data"] : $detail_response, "user_comm" => isset($comm_response["data"]) ? $comm_response["data"] : $comm_response, "status" => $status)

Note: In $user_detail, "user_id" & in $user_comm, "object_type" & "object_id" is not needed as the User is created / updated before Inserting / Updating the UserDetail & UserCommuniation

