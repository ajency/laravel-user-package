<?php

return [
	/*
     All the Custom table / Model Name, type of Migration, Columns to be assigned under Migrations are to be defined below.
     Here are the fields available
     Under Table level,
        "model" -> Model Name & also Migration file is generated & Table Name generated relating to the Model Name
        "table" -> Table name to be assigned for Migrations (Note: Only Migration file is generated)
        "status" -> (create / alter)
        "columns" -> [Array of Columns]
            "columns" -> array( 
                "column" => "<column_name>", 
                "type" => "<column_type>", 
                "size" => "size of the Column - < Only for String Type >",
                "digit" => "Digits to display - < Only for Float Type >", "decimal_pt" => "numbers to store after decimal point - < Only for Float Type >",
                "precision" => "Digits of precision to store < Only for Decimal Type >", "scale" => "Decimal point scale < Only for Decimal Type >",
                "comment" => "<comment_for_the_column>", 
                "nullable" => "<true/false> [Decides whether Column is nullable or not]", 
                "default" => "<default_value> [Sets default value on SAVE]"
            )

            "type" ("For a column") -> ["string", "text", "boolean", "integer", "decimal", "float", "date", "datetime", "timestamp", "increments"]
    */

    array(
        "table" => "users", "status" => "alter", "columns" => [
            array("column" => "type", "type" => "string", "size" => 100, "comment" => "Internal / Registered (has password) / Guest (no password)"),
            array("column" => "has_required_fields_filled", "type" => "boolean", "default" => 0),
            array("column" => "status", "type" => "string", "size" => 50, "nullable" => true),
            array("column" => "creation_date", "type" => "datetime", "nullable" => true),//, "default" => ),
            array("column" => "last_login", "type" => "datetime", "nullable" => true),//, "default" => ),
            array("column" => "signup_source", "type" => "string", "size" => 100, "nullable" => true),
        ]
    ),
    array(
        "table" => "user_details", "model" => "UserDetail", "status" => "create", "columns" => [
            array("column" => "subtype", "type" => "string", "size" => 50, "nullable" => true),
            array("column" => "city", "type" => "string", "size" => 50, "nullable" => true),
            array("column" => "area", "type" => "string", "size" => 50, "nullable" => true),
            array("column" => "is_job_seeker", "type" => "boolean", "default" => 0),
            array("column" => "has_job_listing", "type" => "boolean", "default" => 0),
            array("column" => "has_business_listing", "type" => "boolean", "default" => 0),
            array("column" => "has_restaurant_listing", "type" => "boolean", "default" => 0),
        ]
    )
];