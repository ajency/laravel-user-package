<?php
	/* 
		"roles" ->  Array of Role names to be generated,
		"permissions" -> Array of Permission names to be generated
		"roles_permissions" -> [Array having
			array("role" => < Array index of the role in "roles", "permissions" => [array of <indexes of permssion> from "permissions"])
		]
		
		Example:
		[
			"roles" => ["superadmin", "admin", "member"],
			"permissions" => ["add_users", "edit_users", "add_personal", "edit_personal", "add_internal", "edit_internal"],
			"roles_permissions" => [
				"roles" => 0, "permissions" => [0, 1, 2, 3, 4, 5],
				"roles" => 1, "permissions" => [0, 1, 2, 3],
				"roles" => 2, "permissions" => [2, 3]
			]
		]
	*/
	return [
		"roles" => ['superadmin', 'listing_manager', 'customer'],
		"permissions" => ['add_internal_user', 'edit_internal_user', 'view_internal_user_list', 'add_listing', 'edit_listing', 'manage_categories', 'manage_locations', 'listing_approval', 'add_job', 'edit_job'],
		"roles_permissions" => ["role" => 0, "permissions" => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]],
            ["role" => 1, "permissions" => [3, 4, 7]],
            ["role" => 2, "permissions" => [3, 4, 8, 9]]
        ]
	];