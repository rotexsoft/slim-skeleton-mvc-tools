#!/usr/bin/php
<?php
include_once __DIR__.DIRECTORY_SEPARATOR."cli-script-helper-functions.php";

if( php_sapi_name() !== 'cli' ) {

    exit('Error: This script should only be run via the command line!!');
}

try {
    create_controller($argc, $argv);

} catch(\Exception $e) {
    
    $msg = 'Exception was thrown in ' . $e->getFile() . ' on line ' . $e->getLine()
           . PHP_EOL . $e->getMessage()
           . PHP_EOL . 'Exception Trace:' . PHP_EOL . $e->getTraceAsString();
    echo $msg;
}

/**
 * 
 * @param int $argc
 * @param array $argv
 * 
 * @return void
 * 
 * @throws \InvalidArgumentException
 */
function create_controller($argc, array $argv) {
    
    if( is_string($argc) && is_numeric($argc) ) {
        
        $argc = (int) $argc;
    }
    
    if( !is_int($argc) ) {
        
        $err_msg = 'The expected value for the first argument to '
                   . '`' . __FUNCTION__ . '($argc, array $argv)` should be an int.'
                   . ' `'. ucfirst(gettype($argc)). '` with the value below was supplied:'.PHP_EOL
                   . var_export($argc, true).PHP_EOL.PHP_EOL
                   . 'Good bye!!!';
        printError($err_msg);
        exit;
    }
    
    $ds = DIRECTORY_SEPARATOR;
    
    if ( 
        $argc <= 1
        || in_array('--help', $argv)
        || in_array('-help', $argv)
        || in_array('-h', $argv)
        || in_array('-?', $argv)
    ) {
        displayHelp(basename(__FILE__));

    } else if ( 
       (
            $argc === 5
            && ( in_array('--controller-name', $argv) || in_array('-c', $argv) )
            && ( in_array('--path-to-src-folder', $argv) || in_array('-p', $argv) )  
        )     
        ||
       (
            $argc >= 7
            && ( in_array('--controller-name', $argv) || in_array('-c', $argv) )
            && ( in_array('--path-to-src-folder', $argv) || in_array('-p', $argv) )
            && ( in_array('--extends-controller', $argv) || in_array('-e', $argv) )
        )  
    ) {
        $templates_dir = dirname(__DIR__).$ds.'templates'.$ds;
        $controller_name = getOptVal('--controller-name', $argv);

        if($controller_name === false) {

            $controller_name = getOptVal('-c', $argv);
        }

        $studly_controller_name = \Slim3MvcTools\Functions\Str\dashesToStudly(
                                        \Slim3MvcTools\Functions\Str\underToStudly(
                                            $controller_name
                                        )
                                  );

        $dashed_controller_name = \Slim3MvcTools\Functions\Str\toDashes(
                                        $controller_name
                                  );

        if( !isValidClassName($studly_controller_name) ) {

            printError("Invalid controller class name `$controller_name` supplied. Goodbye!!");
            exit;
        }

        $src_folder_path = getOptVal('--path-to-src-folder', $argv);

        if($src_folder_path === false) {

            $src_folder_path = getOptVal('-p', $argv);
        }

        $src_folder_path = normalizeFolderPathForOs($src_folder_path);

        if( !file_exists($src_folder_path) || !is_dir($src_folder_path) ) {

            printError("The src folder path `$src_folder_path` supplied is a non-existent directory. Goodbye!!");
            exit;
        }

        ////////////////////////////////////////////////////////////////////////////
        $default_controller_2_extend = '\\Slim3MvcTools\\Controllers\\BaseController';

        $controller_2_extend = getOptVal('--extends-controller', $argv);

        if($controller_2_extend === false) {

            $controller_2_extend = getOptVal('-e', $argv);

            if($controller_2_extend !== false) {

                if( !isValidExtendsClassName($controller_2_extend) ) {

                    printError("Invalid controller class name `$controller_2_extend` for extension supplied. Goodbye!!");
                    exit;
                }

            } else {

                //use default controller class to be extended
                $controller_2_extend = $default_controller_2_extend;
            }
        } else {

            if( !isValidExtendsClassName($controller_2_extend) ) {

                printError("Invalid controller class name `$controller_2_extend` for extension supplied. Goodbye!!");
                exit;
            }
        }

        ////////////////////////////////////////////////////////////////////////////
        $namepace_declaration = '';//omit namespace declaration by default
        $namepace_4_controller = getOptVal('--namespace-4-controller', $argv);

        if($namepace_4_controller === false) {

            $namepace_4_controller = getOptVal('-n', $argv);

            if($namepace_4_controller !== false) {

                if( !isValidNamespaceName($namepace_4_controller) ) {

                    printError("Invalid namespace `$namepace_4_controller` supplied. Goodbye!!");
                    exit;
                }

                //validation passed
                $namepace_declaration = "namespace {$namepace_4_controller};";

            } else {
                $namepace_4_controller = '';
            }
        } else {

            if( !isValidNamespaceName($namepace_4_controller) ) {

                printError("Invalid namespace `$namepace_4_controller` supplied. Goodbye!!");
                exit;
            }

            //validation passed
            $namepace_declaration = "namespace {$namepace_4_controller};";
        }

        //read template controller and substitute __TEMPLTATE_CONTROLLER__ with given controller name \Slim3MvcTools\Functions\Str\underToStudly(dashesToStudly($controller_name_from_cli))
        //substitute {{TEMPLTATE_CONTROLLER_VIEW_FOLDER}} with the view folder name \Slim3MvcTools\Functions\Str\toDashes($controller_name_from_cli)
        //write processed controller file to S3MVC_APP_ROOT_PATH.$ds.'src'.$ds.'controllers'.$ds

        //make the dir S3MVC_APP_ROOT_PATH.$ds.'src'.$ds.'views'.$ds.\Slim3MvcTools\Functions\Str\toDashes($controller_name_from_cli)
        //read template controller index view and substitute __TEMPLTATE_CONTROLLER__ with given controller name \Slim3MvcTools\Functions\Str\underToStudly(dashesToStudly($controller_name_from_cli))
        //write processed controller file to S3MVC_APP_ROOT_PATH.$ds.'src'.$ds.'views'.$ds.\Slim3MvcTools\Functions\Str\toDashes($controller_name_from_cli)

        $template_controller_file = $templates_dir.'controller-class-template.php';
        $dest_controller_class_file_folder = $src_folder_path.'controllers'.$ds;
        $dest_controller_class_file = $dest_controller_class_file_folder."{$studly_controller_name}.php";

        if( 
            !file_exists($dest_controller_class_file_folder)
            && !mkdir($dest_controller_class_file_folder, 0775, true)
        ) {
            printError("Failed to create `$dest_controller_class_file_folder`; the folder supposed to contain the controller named `$studly_controller_name`. Goodbye!!");
            exit;
        }

        $template_view_file = $templates_dir.'index-view-template.php';
        $dest_view_file_folder = $src_folder_path.'views'.$ds."{$dashed_controller_name}{$ds}";
        $dest_view_file = "{$dest_view_file_folder}index.php";

        if( 
            !file_exists($dest_view_file_folder)
            && !mkdir($dest_view_file_folder, 0775, true) 
        ) {    
            printError("Failed to create `$dest_view_file_folder`; the folder supposed to contain views for the controller named `$studly_controller_name`. Goodbye!!");
            exit;
        }

        if( file_exists($dest_controller_class_file) ) {

            printError("Controller class `$studly_controller_name` already exists in `$dest_controller_class_file`. Goodbye!!");
            exit;
        }

        if( file_exists($dest_view_file) ) {

            printError("View file `$dest_view_file` already exists for Controller class `$studly_controller_name`. Goodbye!!");
            exit;
        }

        printInfo("Creating Controller Class `$studly_controller_name` in `{$dest_controller_class_file}` ....");

        ////////////////////////////////////////////////////////////////////////////
        $replaces = [
            '__CONTROLLER_2_EXTEND__' => $controller_2_extend,
            '__TEMPLTATE_CONTROLLER__' => $studly_controller_name,
            'namespace __NAMESPACE_2_REPLACE__;' => $namepace_declaration,
            '{{TEMPLTATE_CONTROLLER_VIEW_FOLDER}}' => $dashed_controller_name,
            "'__login_success_redirect_controller__'" => "'{$dashed_controller_name}'",
        ];

        if( processTemplateFile($template_controller_file, $dest_controller_class_file, $replaces) === false ) {

            printError("Failed transforming template controller `$template_controller_file` to `$dest_controller_class_file`. Goodbye!!");

        } else {

            printInfo("Successfully created `{$dest_controller_class_file}` ....".PHP_EOL);
        }

        printInfo("Creating index view for `{$studly_controller_name}::actionIndex()` in `{$dest_view_file}` ....");

        $replaces['__TEMPLTATE_CONTROLLER__'] = rtrim($namepace_4_controller, '\\').'\\'.$studly_controller_name;

        if( processTemplateFile($template_view_file, $dest_view_file, $replaces) === false ) {

            printError("Failed creating index view for `{$studly_controller_name}::actionIndex()` in `{$dest_view_file}`.");
            printInfo("Deleting `{$dest_controller_class_file}` ....");

            if( !unlink($dest_controller_class_file) ) {

                printInfo("Failed to delete `{$dest_controller_class_file}`. Please delete it manually. Goodbye!!");

            } else {

                printInfo("Goodbye!!");
            }

            exit;

        } else {

            printInfo("Successfully created `{$dest_view_file}` ....".PHP_EOL);
        }

        printInfo("All done!! Remember to run `composer dumpautoload` so that composer can pick up the newly created controller class `$studly_controller_name` in `{$dest_controller_class_file}`.");

        //we are done

    } else {

        displayHelp(basename(__FILE__));
    }
}
