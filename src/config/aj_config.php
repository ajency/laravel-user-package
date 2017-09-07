<?php
	
	return [
		"login" => [
			"success_redirect" => "/",
			"failure_redirect" => "/"
		],
		"signup" => [
			"success_redirect" => "/",
			"failure_redirect" => "/"
		],
		"social" => ["google", "facebook"]
		"user_required_fields" = [
			array("table" => "users", "columns" => [""]),
			array("table" => "user_details", "columns" => ["area", "city"]),
			array("table" => "user_communications", "columns" => [])
		]
	];