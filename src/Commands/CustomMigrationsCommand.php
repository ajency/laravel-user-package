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

        $output->writeln("Write Content");
        $output->writeln($content);
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
                    array("column" => "value", "type" => "string", "size" => 250, "nullable" => true),
                    array("column" => "is_primary", "type" => "boolean", "default" => 0),
                    array("column" => "is_communication", "type" => "boolean", "default" => 0),
                    array("column" => "is_verified", "type" => "boolean", "default" => 0),
                    array("column" => "is_visible", "type" => "boolean", "default" => 0),
                ]
            )
        ];

        foreach (config('aj_user_migrations') as $tableKey => $tableVal) {
            if (((isset($tableVal["model"]) && $tableVal["model"] == "UserDetail") || $tableVal["table"] == "user_details") && $tableVal["status"] == "create") {
                array_push($tableVal["columns"], array("column" => "user_id", "type" => "integer", "nullable" => true)); // Push the "user_id" in the UserDetail model's Column list
            }

            $op->writeln(json_encode($tableVal));
            array_push($tables, $tableVal); // Push Custom Table config array to Default Table Array
        }

        // array_unshift($tables, config('aj_user_migrations')); // Prepend Default $tables to the Config Table array


        foreach($tables as $index => $row) {
            if($row['status'] == "create") {
                if(isset($row["model"]) && $row["model"]) { // If model Name is defined
                    $op->writeln("Create via Model");
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

            if(isset($row["model"]) && $row["model"] == "UserDetail" && $row["status"] == "create") {
                $lines = $this->readFromFile("./app/User.php");

                if ($lines["status"]) {
                    $extracted_content = $lines["data"];
                    $user_model_content = "\n\tpublic function getUserDetails() { \n\t\t\$this->hasOne('App\UserDetail', 'user_id');\n\t}\n";
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
                    $user_details_model_content = "\n\tpublic function getUser() { \n\t\t\$this->belongsTo('App\User', 'user_id');\n\t}\n";
                    array_splice($lines["data"], count($extracted_content) - array_search("}\n", array_reverse($extracted_content)) - 1, 0, $user_model_content); // 

                    $content = implode("", $lines["data"]); // Merge all the content
                    $this->writeToFile("./app/".$row["model"].".php", $content);
                }
            }

            $lines = $this->readFromFile("./database/migrations/".$table_name[0].".php")["data"];

            $content = '';

            foreach ($lines as $key => $value) {
                if ($key > 4 && strpos(json_encode($lines[$key - 4]), "up()") && strpos(json_encode($lines[$key - 2]), "Schema::")) { // For migrate => Alter / Create
                    foreach($row["columns"] as $colIndex => $colValue) {
                        if ($colValue['type'] == 'string') {
                            if(isset($colValue['size'])) {
                                $column = "\$table->string('" . $colValue['column'] . "', " . $colValue['size'] . ")";
                            } else {
                                $column = "\$table->string('" . $colValue['column'] . "')";
                            }
                        } else if ($colValue['type'] == 'text') {
                            $column = "\$table->text('" . $colValue['column'] . "')";
                        } else if ($colValue['type'] == 'boolean') {
                            $column = "\$table->boolean('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'integer') {
                            $column = "\$table->integer('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'float') {
                            if(isset($colValue['digit']) && isset($colValue['decimal_pt'])) {
                                $column = "\$table->float('" . $colValue['column'] . "', " . $colValue['digit'] . ", " . $colValue['decimal_pt'] . ")";
                            } else {
                                $column = "\$table->float('" . $colValue['column']. "')";
                            }
                        } else if($colValue['type'] == 'decimal') {
                            if(isset($colValue['precision']) && isset($colValue['scale'])) {
                                $column = "\$table->decimal('" . $colValue['column'] . "', " . $colValue['precision'] . ", " . $colValue['scale'] . ")";
                            } else {
                                $column = "\$table->decimal('" . $colValue['column'] . "')";
                            }
                        } else if($colValue['type'] == 'date') {
                            $column = "\$table->date('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'datetime') {
                            $column = "\$table->dateTime('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'timestamp') {
                            $column = "\$table->timestamp('" . $colValue['column'] . "')";
                        } else if($colValue['type'] == 'increment') {
                            $column = "\$table->increments('" . $colValue['column'] . "')";
                        }

                        // Column Modifiers
                        if (isset($colValue["comment"])) { // Assign if Comment Field is Assigned
                            $column .= "->comment('" . $colValue["comment"] . "')";
                        }
                        if (isset($colValue["default"])) { // Assign if Default Field is Assigned
                            if (is_numeric($colValue["default"])) {
                                $column .= "->default(" . $colValue["default"] . ")";
                            } else {
                                $column .= "->default('" . $colValue["default"] . "')";
                            }
                        }
                        if (isset($colValue["nullable"]) && $colValue["nullable"]) { // Assign if nullable is enabled
                            $column .= "->nullable()";
                        }
                            
                        $content .= "\t\t\t" . $column . ";\n";
                    }
                } else if ($key > 4 && strpos(json_encode($lines[$key - 4]), "down()") && strpos(json_encode($lines[$key - 2]), "Schema::") && !strpos(json_encode($lines[$key - 2]), "Schema::dropIfExists")) { // For Rollback -> assign column names Only if the Migrations Type was ALTER
                    
                    foreach($row["columns"] as $colIndex => $colValue) {
                        $content .= "\t\t\t" . "\$table->dropColumn('" . $colValue['column'] . "');\n";
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