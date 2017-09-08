<?php
	
	return [
		"social_failure_redirect_url" => "/",
		"social" => ["google", "facebook"],
		"table_required_fields" = [
			array("table" => "users", "columns" => ["type", "status", "signup_source"]),
			array("table" => "user_details", "columns" => ["area", "city"]),
			array("table" => "user_communications", "columns" => [])
		]
	];