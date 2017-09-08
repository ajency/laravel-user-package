<?php

namespace Ajency\User\Commands;

use Illuminate\Console\Command;

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $op = new ConsoleOutput();
        
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

        array_push($tables, config('aj_user_migrations')); // Push Custom Table arrays to Array

        /*$tables = [
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
        ];*/

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
            $op->writeln($table_name[0].".php created successfully.");

            $lines = file("./database/migrations/".$table_name[0].".php");

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
            $file = fopen("./database/migrations/".$table_name[0].".php", "w");
            fwrite($file, $content);
            fclose($file);

        }

        $op->writeln("Model & Migration files created successfully");
    }
}
