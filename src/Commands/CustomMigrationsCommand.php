<?php

namespace Ajency\User\Commands;

use Illuminate\Console\Command;

use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;

class CustomMigrationsCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aj_user:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function readFromFile($file_path, $from_last = false) {
        $status = true; $content = NULL;
        $output = new ConsoleOutput();

        $output->writeln(json_encode($file_path));

        try {
            if ($from_last) {
                $content = file($file_path);
            } else {
                $content = file($file_path);
            }
        } catch (Exception $e) {
            $output->writeln($e);
        }

        return array("status" => $status, "data" => $content);
    }

    public function writeToFile($file_path, $content) {
        $status = true;
        $output = new ConsoleOutput();

        try {
            $file = fopen($file_path, "w");
            fwrite($file, $content);
            fclose($file);
        } catch (Exception $e) {
            $output->writeln($e);
            $status = false;
        }

        return array("status" => $status);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $op = new ConsoleOutput();
        
        /*
            $tables = [
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
                ),
                array(
                    "table" => "user_communications", "model" => "UserCommunication", "status" => "create", "columns" => [
                        array("column" => "object_type", "type" => "string", "size" => 50, "nullable" => true),
                        array("column" => "object_id", "type" => "integer", "nullable" => true),
                        array("column" => "type", "type" => "string", "size" => 100, "comment" => "Email / Landline / Mobile", "nullable" => true),
                        array("column" => "value", "type" => "string", "size" => 250, "nullable" => true),
                        array("column" => "is_primary", "type" => "boolean", "default" => 0),
                        array("column" => "is_communication", "type" => "boolean", "default" => 0),
                        array("column" => "is_verified", "type" => "boolean", "default" => 0),
                        array("column" => "is_visible", "type" => "boolean", "default" => 0),
                    ]
                )
            ];
        */
        $tables = [
            array("table" => "user_communications", "model" => "UserCommunication", "status" => "create", "columns" => [
                    array("column" => "object_type", "type" => "string", "size" => 50, "nullable" => true),
                    array("column" => "object_id", "type" => "integer", "nullable" => true),
                    array("column" => "type", "type" => "string", "size" => 100, "nullable" => true, "comment" => "Email / Landline / Mobile"),
                    array("column" => "country_code", "type" => "string", "size" => 250, "nullable" => true), // New column Country - Code
                    array("column" => "value", "type" => "string", "size" => 250, "nullable" => true),
                    array("column" => "is_primary", "type" => "boolean", "default" => 0),
                    array("column" => "is_communication", "type" => "boolean", "default" => 0),
                    array("column" => "is_verified", "type" => "boolean", "default" => 0),
                    array("column" => "is_visible", "type" => "boolean", "default" => 0),
                ]
            ),
            // array("table" => "user_communications", "status" => "alter", "columns" => [
            //         array("column" => "country_code", "type" => "string", "size" => 250, "nullable" => true),
            //     ]
            // )
        ];

        foreach (config('aj_user_migrations') as $tableKey => $tableVal) {
            if (((isset($tableVal["model"]) && $tableVal["model"] == "UserDetail") || $tableVal["table"] == "user_details") && $tableVal["status"] == "create") {
                array_push($tableVal["columns"], array("column" => "user_id", "type" => "integer", "nullable" => true)); // Push the "user_id" in the UserDetail model's Column list
            }

            array_push($tables, $tableVal); // Push Custom Table config array to Default Table Array
        }

        // array_unshift($tables, config('aj_user_migrations')); // Prepend Default $tables to the Config Table array


        foreach($tables as $index => $row) {
            if($row['status'] == "create") {
                if(isset($row["model"]) && $row["model"]) { // If model Name is defined
                    // $op->writeln("Create via Model");
                    $modelName = '';

                    foreach (explode(" ", $row['model']) as $key => $value) {
                        // Replace all the <spaces> in ModelName with \<spaces> -> as Terminal/Shell Script doesn't accept <spaces> between Name
                        if ($key == 0) {
                            $modelName = $value;
                        } else {
                            $modelName .= '\ ' . $value;
                        }
                    }

                    $output = shell_exec('php artisan make:model '. $modelName .' --migration'); // Create a Model with Migration file
                } else { // Use the Table name & only create Migration File
                    $output = shell_exec('php artisan make:migration create_'.$row['table'].'_table --create='.'"'.$row['table'].'"'); // Else just create a migration file
                }
            } else if($row['status'] == "alter") { // Create ALter Migration file under that Table
                $output = shell_exec('php artisan make:migration alter_aj_'.$row['table'].'_table --table='.'"'.$row['table'].'"');
            }

            $op->writeln($output);
            $table_name = explode("\n", explode(": ", $output)[1]);
            //$op->writeln($table_name[0].".php created successfully.");
            
            // $tab_spacing = "\t";
            $tab_spacing = "    "; // 4 x <spaces> instead of \t -> for Content Display

            if(isset($row["model"]) && $row["model"] == "UserDetail" && $row["status"] == "create") { // If UserDetail is being created, then
                $lines = $this->readFromFile("./app/User.php");
                if ($lines["status"]) {
                    $extracted_content = $lines["data"];
                    // $user_model_content = "\n\tpublic function getUserDetails() { \n\t\t\$this->hasOne('App\UserDetail', 'user_id');\n\t}\n";
                    $user_model_content = "\n" . $tab_spacing . "public function getUserDetails() { \n" . $tab_spacing . $tab_spacing . "return \$this->hasOne('App\UserDetail', 'user_id');\n" .$tab_spacing . "}\n";

                    // Heredoc syntax
                    $user_model_content = "";/*<<<EOD
    public function getUserDetails() {
        return \$this->hasOne('App\UserDetail', 'user_id');
    }
EOD;*/ // closing 'EOD' must be on it's own line, and to the left most point
                    array_splice($lines["data"], count($extracted_content) - array_search("}\n", array_reverse($extracted_content)) - 1, 0, $user_model_content); // Insert the above function to the content

                    /*foreach ($extracted_content as $key_ec => $value_ec) {
                        $content .= $value_ec;
                    }*/
                    $content = implode("", $lines["data"]);
                    $this->writeToFile("./app/User.php", $content);
                }
                
                $lines = $this->readFromFile("./app/".$row["model"].".php");
                if($lines["status"]) {
                    $extracted_content = $lines["data"];
                    // $user_details_model_content = "\n\tpublic function getUser() { \n\t\t\$this->belongsTo('App\User', 'user_id');\n\t}\n";
                    $user_details_model_content = "\n" . $tab_spacing . "public function getUser() { \n" . $tab_spacing . $tab_spacing . "return \$this->belongsTo('App\User', 'user_id');\n" . $tab_spacing ."}\n";

                    $user_details_model_content = "";/*<<<EOD
    public function getUser() {
        return \$this->belongsTo('App\User', 'user_id');
    }
EOD;*/
                    array_splice($lines["data"], count($extracted_content) - array_search("}\n", array_reverse($extracted_content)) - 1, 0, $user_details_model_content); // 

                    $content = implode("", $lines["data"]); // Merge all the content
                    $this->writeToFile("./app/".$row["model"].".php", $content);
                }
            } else if(isset($row["model"]) && $row["model"] == "UserCommunication" && $row["status"] == "create") {
                $lines = $this->readFromFile("./app/User.php");
                if ($lines["status"]) {
                    $extracted_content = $lines["data"];
                    
                    $user_model_content = "";/*<<<EOD
    public function getUserCommunications() { // Get all the communication related to that user
        return \$this->hasMany('App\UserCommunication', 'object_id')->where('object_type', 'App\User');
    }

    public function getPrimaryEmail() { // Get the primary Email
        return \$this->hasMany('App\UserCommunication', 'object_id')->where([['object_type','App\User'], ['type', 'email'], ['is_primary', true]]);
    }

    public function getPrimaryContact() { // Get the Primary Contact No
        return \$this->hasMany('App\UserCommunication', 'object_id')->where([['object_type','App\User'], ['is_primary', true]])->whereIn('type', ["telephone", "mobile"]);
    }
EOD;*/
                    array_splice($lines["data"], count($extracted_content) - array_search("}\n", array_reverse($extracted_content)) - 1, 0, $user_model_content); // Insert the above function to the content

                    /*foreach ($extracted_content as $key_ec => $value_ec) {
                        $content .= $value_ec;
                    }*/
                    $content = implode("", $lines["data"]);
                    $this->writeToFile("./app/User.php", $content);
                }
                
                $lines = $this->readFromFile("./app/".$row["model"].".php");
                if($lines["status"]) {
                    $extracted_content = $lines["data"];
                    
                    $user_comm_model_content = "";/*<<<EOD
    public function getUser() { // Get User related to that communication or set of Communications
        return \$this->belongsTo('App\User', 'object_id');
    }
EOD;*/
                    array_splice($lines["data"], count($extracted_content) - array_search("}\n", array_reverse($extracted_content)) - 1, 0, $user_comm_model_content); // 

                    $content = implode("", $lines["data"]); // Merge all the content
                    $this->writeToFile("./app/".$row["model"].".php", $content);
                }
            }

            $lines = $this->readFromFile("./database/migrations/".$table_name[0].".php")["data"];

            $content = '';

            foreach ($lines as $key => $value) {
                if ($key > 4 && strpos(json_encode($lines[$key - 4]), "up()") && strpos(json_encode($lines[$key - 2]), "Schema::")) { // For migrate => Alter / Create
                    foreach($row["columns"] as $colIndex => $colValue) {
                        // Column Type & Column Name
                        if ($colValue['type'] == 'boolean') { // Type <Boolean>
                            $column = "\$table->boolean('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'date') {// Type <Date>
                            $column = "\$table->date('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'datetime') { // Type <DateTime>
                            $column = "\$table->dateTime('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'decimal') { // Type <Decimal>
                            if(isset($colValue['precision']) && isset($colValue['scale'])) {
                                $column = "\$table->decimal('" . $colValue['column'] . "', " . $colValue['precision'] . ", " . $colValue['scale'] . ")";
                            } else {
                                $column = "\$table->decimal('" . $colValue['column'] . "')";
                            }
                        } else if($colValue['type'] == 'float') { // Type <Float>
                            if(isset($colValue['digit']) && isset($colValue['decimal_pt'])) {
                                $column = "\$table->float('" . $colValue['column'] . "', " . $colValue['digit'] . ", " . $colValue['decimal_pt'] . ")";
                            } else {
                                $column = "\$table->float('" . $colValue['column']. "')";
                            }
                        } else if($colValue['type'] == 'increment') { // Type <Increment>
                            $column = "\$table->increments('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'integer') { // Type <Integer>
                            $column = "\$table->integer('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'json') { // Type <JSON>
                            $column = "\$table->json('" . $colValue['column'] . "')";
                        } else if ($colValue['type'] == 'string') { // Type <String>
                            if(isset($colValue['size'])) {
                                $column = "\$table->string('" . $colValue['column'] . "', " . $colValue['size'] . ")";
                            } else {
                                $column = "\$table->string('" . $colValue['column'] . "')";
                            }
                        } else if ($colValue['type'] == 'text') { // Type <Text>
                            $column = "\$table->text('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'timestamp') { // Type <TimeStamp>
                            $column = "\$table->timestamp('" . $colValue['column'] . "')";
                        }

                        // Column Modifiers
                        if (isset($colValue["after"]) && $colValue["after"]) { // Assign if after is enabled -> Only for MySQL
                            $column .= "->after('" . $colValue["after"] . "')"; // Sets the Column after the Defined column
                        }
                        if (isset($colValue["comment"])) { // Assign if Comment Field is Assigned -> Only for MySQL
                            $column .= "->comment('" . $colValue["comment"] . "')"; // Write a Comment in Column Header
                        }
                        if (isset($colValue["default"])) { // Assign if Default Field is Assigned
                            if (is_numeric($colValue["default"])) {
                                $column .= "->default(" . $colValue["default"] . ")";// Set default <Number> value
                            } else {
                                $column .= "->default('" . $colValue["default"] . "')"; // Set default <String> value
                            }
                        }
                        if (isset($colValue["first"]) && $colValue["first"]) { // Assign if first is defined -> Only for MySQL
                            $column .= "->first()"; // Assign the referred column first
                        }
                        if (isset($colValue["nullable"]) && $colValue["nullable"]) { // Assign if nullable is enabled
                            $column .= "->nullable()"; // Sets the Default to NULL i.e. column is NULLABLE
                        }
                            
                        // $content .= "\t\t\t" . $column . ";\n";
                        $content .= $tab_spacing . $tab_spacing . $tab_spacing . $column . ";\n"; // Using <spaces> instead of Tabs (\t)
                    }
                } else if ($key > 4 && strpos(json_encode($lines[$key - 4]), "down()") && strpos(json_encode($lines[$key - 2]), "Schema::") && !strpos(json_encode($lines[$key - 2]), "Schema::dropIfExists")) { // For Rollback -> assign column names Only if the Migrations Type was ALTER
                    
                    foreach($row["columns"] as $colIndex => $colValue) {
                        //$content .= "\t\t\t" . "\$table->dropColumn('" . $colValue['column'] . "');\n";
                        $content .= $tab_spacing . $tab_spacing . $tab_spacing . "\$table->dropColumn('" . $colValue['column'] . "');\n"; // Using <spaces> instead of Tabs (\t)
                    }
                }

                $content .= $value; // Concatenation
            }

            //$op->writeln($content);

            /*$file = fopen("./database/migrations/".$table_name[0].".php", "w");
            fwrite($file, $content);
            fclose($file);*/
            $write_response = $this->writeToFile("./database/migrations/".$table_name[0].".php", $content);

            if($write_response) {
                $op->writeln("Added user defined columns in ".$table_name[0].".php");
            }

        }

        $op->writeln("Model & Migration files created successfully");
    }
}