#!/usr/bin/php
<?php

$ds = DIRECTORY_SEPARATOR;
include_once __DIR__."{$ds}..{$ds}functions{$ds}str-helpers.php";

//print_r(getOptVal('-c', $argv));
//echo PHP_EOL;
//print_r($argv);
//echo PHP_EOL;

if ( 
    $argc <= 1
    || in_array('--help', $argv)
    || in_array('-help', $argv)
    || in_array('-h', $argv)
    || in_array('-?', $argv)
) {
    displayHelp();

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

    $src_folder_path = normalizeFolderPath($src_folder_path);

    if( !file_exists($src_folder_path) || !is_dir($src_folder_path) ) {
        
        printError("The src folder path `$src_folder_path` supplied is a non-existent directory. Goodbye!!");
        exit;
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
    
    displayHelp();
}

function displayHelp() {
    
    $cur_script = basename(__FILE__);
    
    $help = <<<HELP
This is a script intended for creating a controller class and a default index view file in rotexsoft/slim3-skeleton-mvc-app derived projects.

Usage:
  php {$cur_script} [options]

Example:
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which by default extends `\Slim3MvcTools\Controllers\BaseController`)  and a default view in `src/views/foo-bar/index.php`
    
    php {$cur_script} -c foo-bar -p "/var/www/html/my-app/src"
    
    php {$cur_script} --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src"
  
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which extends `\SomeNameSpace\Controller2Extend`) and a default view in `src/views/foo-bar/index.php`
  
    php {$cur_script} -c foo-bar -p "/var/www/html/my-app/src" -e "\\SomeNameSpace\\Controller2Extend"
    
    php {$cur_script} --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src" --extends-controller "\\SomeNameSpace\\Controller2Extend"

Options:
  -h, -?, -help, --help         Display this help message
    
  -c, --controller-name         The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar.
  
  -e, --extends-controller      The name of the controller class (optionally including the name-space prefix) that you want your created controller to extend. `\\Slim3MvcTools\\Controllers\\BaseController` is the default value if this option is not specified. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is the correct full class name).
    
  -n, --namespace-4-controller  The name of the namespace the new controller will belong to. If omitted the namespace declaration will not be present in the new controller class. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is a valid name for a php namespace).
    
  -p, --path-to-src-folder      The absolute path to the `src` folder. Eg. /var/www/html/my-app/src
 
HELP;

    echo printInfo( $help);
}

function getOptVal($opt, array $args) {
    
    $opts_index = array_search($opt, $args);
    
    if( $opts_index === false || !isset($args[++$opts_index]) ) {
        
        return false;
    }
    
    return $args[$opts_index];
}

function isValidClassName($class_name) {
    
    $regex_4_valid_class_name = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    return preg_match( $regex_4_valid_class_name, preg_quote($class_name, '/') );
}

function isValidExtendsClassName($controller_2_extend) {
    
    $extend_class_parts = explode('\\', $controller_2_extend);

    if( strlen($extend_class_parts[0]) <= 0 ) {

        //the extend class name started with a leading \
        //eg. \SomeNameSpace\ControllerClass
        unset($extend_class_parts[0]);
    }

    foreach($extend_class_parts as $class_part) {

        if( !isValidClassName($class_part) ) {

            return false;
        }
    }
    
    return true;
}

function isValidNamespaceName($namepace_4_controller) {
    
    $namespace_parts = explode('\\', $namepace_4_controller);

    if( strlen($namespace_parts[0]) <= 0 ) {

        //the namespace started with a leading \
        //eg. \SomeNameSpace\SubNameSpace
        unset($namespace_parts[0]);
    }

    foreach($namespace_parts as $namespace_part) {

        if( !isValidClassName($namespace_part) ) {

            return false;
        }
    }
    
    return true;
}

function printError($str, $append_new_line = true) {
    
    echo \Slim3MvcTools\Functions\Str\color_4_console( "ERROR: $str", "red",  "black");
    
    if( $append_new_line ) {
        
        echo PHP_EOL;
    }
}

function printInfo($str, $append_new_line = true) {
    
    echo \Slim3MvcTools\Functions\Str\color_4_console( $str, "green",  "black");
    
    if( $append_new_line ) {
        
        echo PHP_EOL;
    }
}

function normalizeFolderPath($path) {
    
    //trim right-most linux style path separator if any
    $trimed_path = rtrim($path, '/');
    
    if( strlen($trimed_path) === strlen($path) ) {
        
        //there was no right-most linux path separator
        //try to trim right-most windows style path separator if any
        $trimed_path = rtrim($trimed_path, '\\');
    }
    
    return $trimed_path . DIRECTORY_SEPARATOR;
}

/**
 * A method that will read a file, run a strtr to replace placeholders with
 * values from our replace array and write it back to the file in $dest.
 *
 * @param string $target the filename of the source
 * @param string $dest the filename of the target
 * @param array $replaces the replaces to be applied to this target
 */
function processTemplateFile($target, $dest, array $replaces)
{
    $retval = file_put_contents(
                    $dest,
                    strtr(
                        file_get_contents($target),
                        $replaces
                    )
                );
    
    if( $retval !== false ) {
        
        chmod($dest, 0755);
    }
   
    return $retval;
}