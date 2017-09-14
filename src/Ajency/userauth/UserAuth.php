<?php

namespace Ajency\User\Ajency\userauth;

use Laravel\Socialite\Contracts\User as ProviderUser;

use Exception;

use App\User;
use App\UserCommunication;
use App\UserDetail;
use Ajency\User\Ajency\socialaccount\SocialAccountService;

use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Output\ConsoleOutput;

class UserAuth {
	public function checkIfUserExists($data) {//, $getObject=false) {
        $user = NULL;
        
        try {
            if (isset($data["email"])) {
                $comm = UserCommunication::where('value','=',$data['email'])->first(); // Check if this email ID exist in the User Communication DB
                $user = $comm ? User::where('id', '=', $comm->object_id)->first() : NULL;
            } else if (isset($data["contact"])) {
                $comm = UserCommunication::where('value','=',$data['contact'])->first(); // Check if this Contact No (Phone No / Landline) exist in the User Communication DB
                $user = $comm ? User::where('id', '=', $comm->object_id)->first() : NULL;
            } /*else {
                $user = User::where('email', '=', $data['username'])->first(); // Check if this Username exist in the User DB
            }*/
            if($user == NULL && isset($data["username"])) {
               $user = User::where('email', '=', $data['username'])->first(); // Check if this Username exist in the User DB 
            }
        } catch (Exception $e) {
            $user = NULL;
            $comm = NULL;
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
        if ($data && in_array($data["provider"], config('aj_user_config.social_account_provider'))) { // If the Sign in / Sign up flow is via Social Account then the account is by default Valid
            return true;
        } else { // The the signIn or SignUp flow is not via Social account signup, then

        	/*$pattern = "/[a-zA-Z0-9_-.+]+@[a-zA-Z0-9-]+.[a-zA-Z]+/";
        	$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';
        	 preg_match($pattern, $data["username"]);*/

        	if (filter_var($data["username"], FILTER_VALIDATE_EMAIL) && checkdnsrr(explode("@", $data["username"])[1])) { // Check if email-ID / username entered is Valid
	            $user_obj = User::where('email', '=', $data["username"]);

	            if($user_obj->count() > 0) {
	                $user_obj = $user_obj->first();

	                if (isset($data["password"]) && Hash::check($data["password"], $user_obj->password)) {
	                    return true;
	                } else {
	                    return false;
	                }
	            } else {
	                return false;
	            }
	        } else {
	        	return false;
	        }
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
            $user_object = $this->checkIfUserExists($data);
            $response_data["user"] = $user_object;

            $response_data["authentic_user"] = $this->isValidUser($data); // Checks if the User-ID (& password {if it is Email Signup}) entered is matching
            
            if ($user_object && $provider == $user_object->signup_source && $user_object->status == "active") { // If user_object is Received & the Signup source provider is same then check if the required Fields are filled
                $response_data["required_fields_filled"] = $this->checkUserFilledRequiredFields($user_object);
                $response_data["status"] = "success";
                $response_data["message"] = "account_found";
            } else {
                $response_data["status"] = "error";

                if ($user_object && $provider !== $user_object->signup_source) { // If User_Object exist & the Signup source is not correct, then return message with "Login with X Source"
                    $response_data["message"] = "is_" . $user_object->signup_source . "_account";
                } else if($user_object && $user_object->status == "suspended") {
                    $response_data["message"] = "account_suspended";
                } else if($user_object && $user_object->status == "inactive") {
                    $response_data["message"] = "email_confirm";
                } else {
                    $response_data["message"] = "no_account";
                }
            }

        } catch(Exception $e) {
            $response_data["status"] = "error";
        }

        return $response_data;
    }

    public function updateOrCreateUserComm($user_obj, $data) {
    	$response_data = [];

        $output = new ConsoleOutput;

    	if (isset($data['email']) || isset($data['contact'])) { // If mobile, landline or Email is defined in the plugin, then mark this fields as 'True' as this is User's 1st contact
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
	                $comm->object_id = $user_obj->id;
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

    public function updateOrCreateUserDetails($user_obj, $data, $search_by_column='user_id', $search_column_value='') {
    	$response_data = []; $details = null;

    	try {
	    	$details = UserDetail::where($search_by_column, '=', $search_column_value); // Get the UserDetail object
	        	
	    	if($details->count() > 0) { // Update Query, if the count is greater than ZERO
	    		/*$details = $details->update([
	    			'is_primary' => $data["is_primary"], 
	    			'is_communication' => $data["is_communication"], 
	    			'is_verified' => $data["is_verified"], 
	    			'is_visible' => $data["is_visible"]
	    		]);*/

	    		// unset($data[$type]); // Remove the Email / Contact from the 
	    		foreach($data as $datak => $datav) { // Update all the fields defined in the JSON data
	    			$details[$datak] = $datav;
	    		}

	    		$details->save();
	    	} else { // Insert Query
	            $details = new UserDetail;
	            
	            $details->user_id = $user_obj->id;

				foreach($data as $datak => $datav) { // Update all the fields defined in the JSON data
	    			$details[$datak] = $datav;
	    		}

	            $details->save();
	    	}
	        

	        $response_data = array("status" => "success", "data" => $details);
    	} catch (Exception $e) {
    		$response_data = array("status" => "error", "data" => $details, "message" => $e);
    	}

        return $response_data;
    }

    public function updateOrCreateUser($user_data, $detail_data = [], $comm_data = []) {
        $detail_response = NULL; $comm_response = NULL;

    	try {
	        $output = new ConsoleOutput;
	        $object = $this->checkIfUserExists($user_data); // Check if the EMail ID exist
            $status = "success";
	        $user_required_params = ['name', 'username', 'password', 'provider', 'status'];

	        $status_active_provider = config("aj_user_config.social_account_provider");
            if (!$object) { // if the email & info is not present in the list, then create new
                $user = new User;

                $user->name = $user_data["name"];
	            $user->email = $user_data["username"];
                $user->password = $user_data["password"];
                $user->signup_source = $user_data['provider'];
                $user->status = in_array($user_data["provider"], $status_active_provider) ? "active" : "inactive"; // If provider is in the List, then activate, else Inactive
                
                /*foreach ($user_required_params as $keyParam => $valueParam) {
	            	unset($user_data[$value_param]); // Remove other fields & it's value from JSON data
	            }

	            foreach($user_data as $datak => $datav) { // Update all the fields defined in the JSON data
                    if(!in_array($datak, $user_required_params)) { // If the key in Array / JSON is not Email or Contact, then UPDATE that value of that Email or Contact
                        $user[$datak] = $datav;
                    }
        		}*/
                $user->save();
	        } else { // This User exist
	           $user = User::find($object["data"]->object_id);
        		
        		if(isset($user_data['username'])) {
	            	$user->email = $user_data["username"];
        		}

        		/*
        		 $user->name = isset($user_data["name"]) ? $user_data["name"] : $user->name;
	             $user->password = isset($user_data["password"]) ? $user_data["password"] : $user->password;
	             $user->signup_source = isset($user_data['provider']) ? $user_data['provider'] : $user->signup_source;
	             $user->status = isset($user_data["status"]) ? $user_data["status"] : in_array($user_data["provider"], $status_active_provider) ? "active" : "inactive";
	            */

	            foreach($user_data as $datak => $datav) { // Update all the fields defined in the JSON data
        			if(!in_array($datak, $user_required_params)) { // If the key in Array / JSON is not Email or Contact, then UPDATE that value of that Email or Contact
        				$user[$datak] = $datav;
        			}
        		}

	            $user->save();
	        }
            if(sizeof($detail_data) > 0) {
            	$detail_response = $this->updateOrCreateUserDetails($user, $detail_data, 'user_id', $user->id);
                $status = ($detail_response["status"] == "success") ? $status : "error";
            }

            if(sizeof($comm_data) > 0) {
                $comm_response = $this->updateOrCreateUserComm($user, $comm_data);
                $status = ($comm_response["status"] == "success") ? $status : "error";
            }

	    } catch(Exception $e) {
            $status = "error";
	    }

        return array("user" => $user, "user_details" => isset($detail_response["data"]) ? $detail_response["data"] : $detail_response, "user_comm" => isset($comm_response["data"]) ? $comm_response["data"] : $comm_response, "status" => $status);
    }

    public function getUserData($user_data, $is_id = false) { // Get all the User related details 

    	/*$status = "success";
    	$message = "";*/
    	$user = NULL;
    	$user_details = NULL;
    	$user_comm = NULL;

    	try {

	    	if(!$is_id) {
	    		$id = $user_data->id;
	    	} else {
	    		$id = $user_data;
	    	}

	    	$user = User::find($id);
	    	try {
	    		$user_details = $user->getUserDetails(); // Gets that Specific Data One-to-One Relation		
	    	} catch (Exception $e) {
	    		$user = UserDetail::where('user_id', '=', $user->id);
	    	}
	    	$user_comm = UserCommunication::where(['object_id', '=' , $id], ['object_type', '=', 'user']);
    	} catch (Exception $e) {
    		$user = NULL;
	    	$user_details = NULL;
	    	$user_comm = NULL;
    	}

    	//return array("user" => $user, "user_details" => $user_details, "user_comm" => $user_comm, "status" => $status, "message" => $message);
    	return array("user" => $user, "user_details" => $user_details, "user_comm" => $user_comm);
    }
}