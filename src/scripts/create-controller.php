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
    $argc >= 5
    && ( in_array('--controller-name', $argv) || in_array('-c', $argv) )
    && ( in_array('--path-to-src-folder', $argv) || in_array('-p', $argv) )
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

    //read template controller and substitute TEMPLTATE_CONTROLLER with given controller name \Slim3MvcTools\Functions\Str\underToStudly(dashesToStudly($controller_name_from_cli))
    //substitute {{TEMPLTATE_CONTROLLER_VIEW_FOLDER}} with the view folder name \Slim3MvcTools\Functions\Str\toDashes($controller_name_from_cli)
    //write processed controller file to S3MVC_APP_ROOT_PATH.$ds.'src'.$ds.'controllers'.$ds
    
    //make the dir S3MVC_APP_ROOT_PATH.$ds.'src'.$ds.'views'.$ds.\Slim3MvcTools\Functions\Str\toDashes($controller_name_from_cli)
    //read template controller index view and substitute TEMPLTATE_CONTROLLER with given controller name \Slim3MvcTools\Functions\Str\underToStudly(dashesToStudly($controller_name_from_cli))
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
    
    $replaces = [
        'TEMPLTATE_CONTROLLER' => $studly_controller_name,
        '{{TEMPLTATE_CONTROLLER_VIEW_FOLDER}}' => $dashed_controller_name
    ];
    
    if( processTemplateFile($template_controller_file, $dest_controller_class_file, $replaces) === false ) {
        
        printError("Failed transforming template controller `$template_controller_file` to `$dest_controller_class_file`. Goodbye!!");
        
    } else {
        
        printInfo("Successfully created `{$dest_controller_class_file}` ....".PHP_EOL);
    }
    
    printInfo("Creating index view for `{$studly_controller_name}::actionIndex()` in `{$dest_view_file}` ....");
        
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
# either of the commands below will create a controller with the class named FooBar in `src/controllers/FooBar.php` and a default view in `src/views/foo-bar/index.php`  
  php {$cur_script} -c foo-bar -p "/var/www/html/my-app/src"
  php {$cur_script} --controller-name foo-bar

Options:
  -h, -?, -help, --help     Display this help message
  -c, --controller-name     The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar.
  -p, --path-to-src-folder  The absolute path to the `src` folder. Eg. /var/www/html/my-app/src
 
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
    
    return preg_match($regex_4_valid_class_name, $class_name);
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

function normalizeFolderPath($path){
    
    return rtrim(rtrim($path, '/'), '\\').DIRECTORY_SEPARATOR;
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