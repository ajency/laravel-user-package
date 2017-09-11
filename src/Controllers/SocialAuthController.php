<?php

namespace App\Http\Controllers\Ajency\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use Socialite;
use App\User;
use Exception;
use Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;


use Symfony\Component\Console\Output\ConsoleOutput;

/* Plugin Access Headers */
use Ajency\User\Ajency\socialaccount\SocialAccountService;
use Ajency\User\Ajency\userauth\UserAuth;

class SocialAuthController extends Controller {
    public function urlSocialAuthRedirect($provider) { // for Provider authentication -> Provider = ['Google', 'Facebook']
        //Session::put('url.failed', URL::previous());
        return Socialite::driver($provider)->redirect();
    }

    public function urlSocialAuthCallback(SocialAccountService $service, Request $request, $provider) { // after 'Provider' authentication & redirection
        
        /*$url = Session::get('url.failed', url('/'));
        Session::forget('url.failed');*/
        $userauthObj = new UserAuth;

        if (! $request->input('code')) {
        	return redirect(config('aj_user_config.social_failure_redirect_url')."?login=true&message=social_permission_denied"); // Redirect to Fail user defined URL
        } else {
            $account = Socialite::driver($provider)->stateless()->user(); /* trying to use socialite on a laravel with socialite sessions deactivated */
        }

        $data = $service->getSocialData($account, $provider);
        $valid_response = $userauthObj->validateUserLogin($data["user"], $provider);
        
        /*
         "$response" => Returns [
            'status' -> Status of the Response, 
            'user' -> User Object from DB,
            'authentic_user' -> If the Logged-In source of the User is Authentic (as in If it is "SocialAccount" then it is "Authentic" by Default; else If "Email Signup", then "Email verification" is necessary & if verified, then the account is "Authentic".),
            'required_fields_filled' -> Flag that defines if the required fields are Filled by User or Not
         ]
        */

        if($valid_response["status"] == "success") {
            if ($valid_response["authentic_user"]) { // If the user is Authentic, then Log the user in
                if(!$valid_response["user"]) { // If $valid_response["user"] == None, then Create/Update the User, User Details & User Communications
                    $user_resp = $userauthObj->updateOrCreateUser($social_data["user"], [], $social_data["user_comm"]);
                } else {
                    $user_resp = $userauthObj->getUserData($valid_response["user"]);
                }

                if($user_resp["status"] == "success") {
                    return ;
                } else {
                    return redirect(config('aj_user_config.social_failure_redirect_url')."?message=");
                }
            }
        } else { //status == "error"
            return redirect(config('aj_user_config.social_failure_redirect_url')."?login=true&message=".$valid_response["message"]); // Redirect to Fail user defined URL
        }
    }
    
    

    public function apiSocialAuth(Request $request, $provider) {
        try {
            //$output = new ConsoleOutput();

            $userauthObj = new UserAuth;
            $service = new SocialAccountService;

            $token = $request->token;//"ya29.Glu3BER1pDE1i7Y77B7IiDo_He-Z-zcsZqs193WTR57qTGO4Lw3a2XnGjJO_PLjGGs4H-Qvjexh_KdEuNCWL1SjRfyQoiXe0oJfbBJg3BC6LL22FE1Onwjfm7GC9";
            $account = Socialite::driver($provider)->userFromToken($token);
            
            $data = $service->getSocialData($account, $provider);
            $valid_response = $userauthObj->validateUserLogin($data, $provider);

            if($valid_response["status"] == "success") {
                if ($valid_response["authentic_user"]) { // If the user is Authentic, then
                    if(!$valid_response["user"]) { // If $valid_response["user"] == None, then Create/Update the User, User Details & User Communications
                        $user_resp = $userauthObj->updateOrCreateUser($social_data["user"], [], $social_data["user_comm"]);
                    } else {
                        $user_resp = $userauthObj->getUserData($valid_response["user"]);
                    }

                    if($user_resp["status"] == "success") {
                        if ($valid_response["required_fields_filled"]) { // If the required fields are filled
                            return response()->json(array("next_url" => "", "status" => 200, "message" => "", "data" => "")); // Data should have JSON of USer, User Details & User Communication
                        } else { // Required fields are not Filled
                            return response()->json(array("next_url" => "", "status" => 200, "message" => "", "data" => ""));
                        }
                    } else {
                        return response()->json(array("next_url" => "", "status" => 400, "message" => "", "data" => ""));
                    }
                } else { // User account is not Authenticated
                    return response()->json(array("next_url" => "", "status" => 403, "message" => $valid_response["message"])); // Unauthorized
                }
        } else { //status == "error"
            return response()->json(array("next_url" => "", "status" => 400, "message" => "")); // Bad Request
        }

        } catch (Exception $e) {
            
        }
    }
}