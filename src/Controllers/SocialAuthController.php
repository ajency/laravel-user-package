<?php

namespace App\Http\Controllers\Ajency\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use Socialite;
use App\SocialAccountService;
use App\User;

use Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

use Symfony\Component\Console\Output\ConsoleOutput;

class SocialAuthController extends Controller {
    public function urlSocialAuthRedirect($provider) { // for Provider authentication -> Provider = ['Google', 'Facebook']
        //Session::put('url.failed', URL::previous());
        return Socialite::driver($provider)->redirect();
    }

    public function urlSocialAuthCallback(SocialAccountService $service, Request $request, $provider) { // after 'Provider' authentication & redirection
        
        /*$url = Session::get('url.failed', url('/'));
        Session::forget('url.failed');*/

        if (! $request->input('code')) {
        	return redirect(config('aj_user_config.social_failure_redirect_url')); // Redirect to that URL
        } else {
            $account = Socialite::driver($provider)->stateless()->user(); /* trying to use socialite on a laravel with socialite sessions deactivated */
        }

        $data = $service->getSocialData($account, $provider);
        
        $reponse = $this->validateUserLogin($data, $provider);
    }
    
    

    public function apiSocialAuth(Request $request, $provider) {
        try {
            $output = new ConsoleOutput();


            $token = $request->token;//"ya29.Glu3BER1pDE1i7Y77B7IiDo_He-Z-zcsZqs193WTR57qTGO4Lw3a2XnGjJO_PLjGGs4H-Qvjexh_KdEuNCWL1SjRfyQoiXe0oJfbBJg3BC6LL22FE1Onwjfm7GC9";
            $account = Socialite::driver($provider)->userFromToken($token);
            
            $service = new SocialAccountService();
            $data = $service->getSocialData($account, $provider);

            $reponse = validateUserLogin($data, $provider);

        } catch (Exception $e) {
            
        }
    }
}