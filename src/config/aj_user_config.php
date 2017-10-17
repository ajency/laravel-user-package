<?php
	/* 
		"social_failure_redirect_url" ->  Redirect to Defined path on Failure / Error,
		"social" -> List of social account that will be used in the project
		"social_email_domain" -> The email domain that is append to all the Social account Username. Hence the username will be "<SocialAccount_ID>@<social_email_domain><Provider>.com" ex: 123192812139001@<social_email_domain>google.com
		"table_required_fields" -> This fields checks if the required columns of the Table are satisfied & if not then the "required_field" check returns False, else True
		"needs_roles_permissions" -> Checks if user wants the Roles-Permission feature from package
	*/
	return [
		"social_failure_redirect_url" => "/",
		"social_account_provider" => ["google", "facebook"], // Social account Domains that are considered for now
		"social_email_domain" => "aj",
		"table_required_fields" => [
			array("table" => "user_details", "columns" => ["area", "city"], "column_relating_to_user" => "user_id"),
			array("table" => "user_communications", "columns" => ["object_type", "type", "value"], "column_relating_to_user" => "object_id")
		],
		"needs_roles_permissions" => true
	];