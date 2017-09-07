<?php

namespace App;

use Laravel\Socialite\Contracts\User as ProviderUser;

use App\UserCommunication;
use Symfony\Component\Console\Output\ConsoleOutput;

class UserAuth {
	public function checkIfUserExists($data) {//, $getObject=false) {
        $user = NULL;

        if (isset($data["email"])) {
            $comm = UserCommunication::where('value','=',$data['email'])->first(); // Check if this email ID exist in the User Communication DB
            $user = User::where('id', '=', $comm->object_id)->first();
        } else if (isset($data["contact"])) {
            $comm = UserCommunication::where('value','=',$data['contact'])->first(); // Check if this Contact No (Phone No / Landline) exist in the User Communication DB
            $user = User::where('id', '=', $comm->object_id)->first();
        } else {
            $user = User::where('email', '=', $data['username'])->first(); // Check if this Username exist in the User DB
        }
        /*
            if($comm) {
                $exist = true;
            } else {
                $exist = false;
            }

            if ($getObject) { // Pass the User object & Boolean Status
                return array("data" => $comm, "status" => $exist);
            } else { // Pass Boolean Status
                return $exist;
            }
        */
        return $user;
    }

    public function isValidUser($data) { // Check if the User is Authenticated
        if ($data && in_array($user->data["provider"], config('aj_user_config.social'))) {
            return true;
        } else {
            return false;
        }
    }

    public function checkUserFilledRequiredFields($user) { // Checks if the required Fields flag is selected or not
    	$tables_n_cols = config('aj_config.user_required_fields');

        if($user && $user->has_required_fields_filled) {
            return array("filled_required" => true, "required_fields" => []);
        } else {

            return array("filled_required" => false, "required_fields" => []);// Array of all the Fields not Filled
        }
    }

    public function updateRequiredFields($user) {
    	// Update the "Required fields" Flag to True based on whether the Required Fields in the User_DEtails table are Filled or Not
    }

    public function validateUserLogin($data, $provider) { // Validate if User is Authenticated & has all the required fields
        $response_data = [];
        
        try {
            $service = new SocialAccountService;

            $response_data["authentic_user"] = $this->isValidUser();

            $user_object = $service->checkIfUserExists($data);
            $response_data["user"] = $user;
            
            if ($user_object) {
                $response_data["required_fields_filled"] = $this->checkUserFilledRequiredFields($user_object);

            $response_data["status"] = "success";
        } catch(Exception $e) {
            $response_data["status"] = "error";
        }

        return $response_data;
        }
    }
}