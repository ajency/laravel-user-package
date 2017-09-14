<?php

namespace Ajency\User\Ajency\socialaccount;

use Laravel\Socialite\Contracts\User as ProviderUser;

use Illuminate\Support\Facades\Hash;
use App\User;
use App\UserCommunication;
use Symfony\Component\Console\Output\ConsoleOutput;

class SocialAccountService {
    /**
    * This function receives the Socialite User Object (post Social account Login) & 
    * returns an Array (or JSON) with keys: "user" & "user_comm".
    * Key "user" will have an array with values for "username", "name", "email", "provider", "password" (Randomly generated string & then Hashed).
    * Key "user_comm" will have an array with values for "email", "is_primary", "is_communication", "is_verified", "is_visible" where in the last 4 fields are Boolean & is set to True,
    * and the other possible fields are "contact" & "contact_type" which will contain Contact Number & Contact Number type (mobile / telephone)
    *
    * This function @return
    *   array(
    *       "user" => array(
    *           "username" => <Social account ID>@<Email_DOmain_in_Config><Social login provider>.in, // -> Provider -> google, facebook, twitter, linkedin, github, bitbucket
    *            "name" => <user's name>, "password" => Hash(random(10)),
    *           "provider" => <Social login provider>, "email" => <Social account Login Email>
    *       ),
    *       "user_comm" => array(
    *           "email" => <Social account Login Email>, "contact" => +<country code> <contact no>, "contact_type" => <mobile / telephone>,
    *           "is_primary" => <true \ false>, "is_communication" => <true \ false>, "is_verified" => <true \ false>, "is_visible" => <true \ false>
    *       )
    *    )
    */
    public function getSocialData(ProviderUser $providerUser, $provider) {
        $email_domain = config('aj_user_config.social_email_domain');

        $response_data = array(
            "user" => array(
                "username" => ((string)$providerUser->id).'@'.$email_domain.strtolower($provider).".in", 
                "name" => $providerUser->name, 
                "password" => Hash::make(str_random(10)), 
                "provider" => $provider,
                "email" => $providerUser->email,
            ),
            "user_comm" => array(
                "email" => $providerUser->email, 
                "is_primary" => true,
                "is_communication" => true,
                "is_verified" => true,
                "is_visible" => true
            )
        );

        if (property_exists($providerUser, "contact")) {//(isset($providerUser["contact"]))
            $response_data["user"]["contact"] = $providerUser->contact;
            $response_data["user"]["contact_type"] = "mobile";
            $response_data["user_comm"]["contact"] = $providerUser->contact;
            $response_data["user_comm"]["contact_type"] = "mobile";
        }

        return $response_data; // JSON Response
    }

   

    /*public function getOrCreateUser($data) {

        $output = new ConsoleOutput();
        $object = $this->checkIfUserExists($data, true); // Check if the EMail ID exist
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

            if (isset($data['email']) || isset($data['contact'])) { // If contact or Email is defined in the plugin, then mark this fields as 'True' as this is User's 1st contact
                $types = [];

                (isset($data['email']) && $data['email']) ? array_push($types, 'email') : '';// If email field exist & the value is not NULL
                (isset($data['contact']) && $data['contact']) ? array_push($types, 'contact') : '';// If contact field exist & the value is not NULL

                foreach ($types as $key => $type) { // Loop through Communication types
                    $comm = new UserCommunication;
                    $comm->object_id = $user->id;
                    $comm->object_type = 'user';

                    $comm->type = $type;
                    $comm->value = $data[$type];
                    
                    $comm->is_primary = true;
                    $comm->is_communication = true;
                    $comm->is_verified = true;
                    $comm->is_visible = true;
        
                    $comm->save();
                }
            }

            $status = "present";
        } else { // This email exist
            $user = User::find($object["data"]->object_id);

            if ($user->signup_source !== $data['provider']) {
                $status = "different";
            }
        }

        return array($user, $status);
    }*/
}