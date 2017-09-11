<?php

namespace Ajency\User\Ajency\userauth;

use Laravel\Socialite\Contracts\User as ProviderUser;

use Exception;

use App\User;
use App\UserCommunication;
use Ajency\User\Ajency\socialaccount\SocialAccountService;
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
        if ($data && in_array($data["provider"], config('aj_user_config.social'))) {
            return true;
        } else {
            return false;
        }
    }

    public function checkUserFilledRequiredFields($user) { // Checks if the required Fields flag is selected or not
    	$tables_n_cols = config('aj_user_config.table_required_fields');

        if($user && $user->has_required_fields_filled) {
            return array("filled_required" => true, "required_fields" => []);
        } else {

            return array("filled_required" => false, "required_fields" => []);// Array of all the Fields not Filled
        }
    }

    public function updateRequiredFields($user) { // Pass User object
    	// Update the "Required fields" Flag to True based on whether the Required Fields in the User_Details table are Filled or Not

    	if(!$user->has_required_fields_filled) {
    		$user->has_required_fields_filled = true;
    		$user->save();
    	}

    	return $user->has_required_fields_filled;
    }

    public function validateUserLogin($data, $provider) { // Validate if User is Authenticated & has all the required fields
        $response_data = [];

        $output = new ConsoleOutput;
        
        try {
            $response_data["authentic_user"] = $this->isValidUser($data);

            $user_object = $this->checkIfUserExists($data);
            $response_data["user"] = $user_object;
            
            if ($user_object) {
                $response_data["required_fields_filled"] = $this->checkUserFilledRequiredFields($user_object);
            }
            $response_data["status"] = "success";
        } catch(Exception $e) {
            $response_data["status"] = "error";
        }

        return $response_data;
    }

    public function updateOrCreateUserComm($data) {
    	$response_data = [];

    	if (isset($data['email']) || isset($data['mobile'] || isset($data['landline'])) { // If mobile, landline or Email is defined in the plugin, then mark this fields as 'True' as this is User's 1st contact
            $types = [];

            (isset($data['email']) && $data['email']) ? array_push($types, 'email') : ''; // If email field exist & the value is not NULL
            (isset($data['contact']) && isset($data['contact']) && $data['contact']) ? array_push($types, 'contact') : ''; // If contact field exist & the value is not NULL

            foreach ($types as $key => $type) { // Loop through Communication types
            	$comm = UserCommunication::where('value','=',$data[$type]); // Get the UserComm object
            	if($comm->count() > 0) { // Update Query, if the count is greater than ZERO
            		/*$comm = $comm->update([
            			'is_primary' => $data["is_primary"], 
            			'is_communication' => $data["is_communication"], 
            			'is_verified' => $data["is_verified"], 
            			'is_visible' => $data["is_visible"]
            		]);*/

            		// unset($data[$type]); // Remove the Email / Contact from the 
            		foreach($data as $datak => $datav) { // Update all the fields defined in the JSON data
            			if(!in_array($datak, $types)) { // If the key in Array / JSON is not Email or Contact, then UPDATE that value of that Email or Contact
            				$comm[$datak] = $datav;
            			}
            		}

            		$comm->save();
            	} else { // Insert Query
	                $comm = new UserCommunication;
	                $comm->object_id = $user->id;
	                $comm->object_type = 'user';

	                // If type == contact then ("contact_type" exist then $data["contact_type"] else "mobile") Else "Email" / $type
	                $comm->type = ($type == "contact") ? (isset($data["contact_type"]) ? $data["contact_type"]: "mobile") : $type; 
	                $comm->value = $data[$type];
	                
	                $comm->is_primary = isset($data["is_primary"]) ? $data['is_primary'] : false;
	                $comm->is_communication = isset($data["is_communication"]) ? $data['is_communication'] : false;
	                $comm->is_verified = isset($data["is_verified"]) ? $data['is_verified'] : false;
	                $comm->is_visible = isset($data["is_visible"]) ? $data['is_visible'] : false;
	    
	                $comm->save();
            	}
            }

            $response_data = array("status" => "success", "data" => $comm);
        } else { // Else required parameters are not passed
        	$response_data = array("status" => "error", "message" => "Please pass the following Required parameters: 'email', 'contact', 'object_id' & 'object_type'.");
        }

        return $response_data;
    }

    public function getOrCreateUser($data) {

        $output = new ConsoleOutput();
        $object = $this->checkIfUserExists($data); // Check if the EMail ID exist
        $status = "exist";

        $status_active_provider = ["google", "facebook"];

        if (!$object["status"]) { // if the email & info is not present in the list, then create new
            $user = new User;
            $user->name = $data["name"];
            $user->email = $data["username"];
            $user->password = $data["password"];
            $user->signup_source = $data['provider'];
            $user->status = in_array($data["provider"], $status_active_provider) ? "active" : "inactive"; // If provider is in the List, then activate, else Inactive
            $user->save();

            $this->updateOrCreateUserComm($data);

            $status = "present";
        } else { // This email exist
            $user = User::find($object["data"]->object_id);

            if ($user->signup_source !== $data['provider']) {
                $status = "different";
            }
        }

        return array($user, $status);
    }
}