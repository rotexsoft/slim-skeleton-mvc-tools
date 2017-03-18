<?php
include_once __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."functions".DIRECTORY_SEPARATOR."str-helpers.php";

/**
 * 
 * 
 * 
 * @param string $cur_script
 * 
 * @return void
 * 
 * @throws \InvalidArgumentException
 */
function displayHelp($cur_script) {
    
    if( !is_string($cur_script) ) {
        
        $msg = 'The expected value for the first argument to '
             . '`' . __FUNCTION__ . '($cur_script)` should be a String value.'
             . ' `' . ucfirst(gettype($cur_script)) . '` with the value below was supplied:'
             . PHP_EOL . var_export($cur_script, true) . PHP_EOL;
        
        throw new \InvalidArgumentException($msg);
    }    
    
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
    
  -c, --controller-name         The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar. This option REQUIRES at least the `-p` or `--path-to-src-folder` option to work.
  
  -e, --extends-controller      The name of the controller class (optionally including the name-space prefix) that you want your created controller to extend. `\\Slim3MvcTools\\Controllers\\BaseController` is the default value if this option is not specified. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is the correct full class name). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.
    
  -n, --namespace-4-controller  The name of the namespace the new controller will belong to. If omitted the namespace declaration will not be present in the new controller class. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is a valid name for a php namespace). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.
    
  -p, --path-to-src-folder      The absolute path to the `src` folder. Eg. `/var/www/html/my-app/src`. This option REQUIRES at least the `-c` (or `--controller-name`) option to work.
 
HELP;
    echo printInfo( $help);
}

/**
 * 
 * 
 * 
 * @param string $opt
 * @param array $args a non-associative array of strings [ 'key1', 'val1', .... 'keyN', 'valN']. 
 *                    Values with even indices are the keys while values with odd indices are the
 *                    values. 
 *                    For example, $args[0] is the key to $args[1], $args[2] is the key to $args[3], etc.
 * 
 * @return boolean|string
 * 
 * @throws \InvalidArgumentException
 */
function getOptVal($opt, array $args) {
    
    $search_key = (is_numeric($opt)) ? '' . $opt : $opt; //convert to string if numeric
    
    if( !is_string($search_key) ) {
        
        $msg = 'The expected value for the first argument to '
             . '`' . __FUNCTION__ . '($opt, array $args)` should be a String value.'
             . ' `'. ucfirst(gettype($search_key)). '` with the value below was supplied:'
             . PHP_EOL . var_export($search_key, true) . PHP_EOL;
        
        throw new \InvalidArgumentException($msg);
    }  
    
    $opts_index = array_search($search_key, $args);
    
    if( $opts_index === false || !isset($args[++$opts_index]) ) {
        
        return false;
    }
    
    return $args[$opts_index];
}

/**
 * 
 * 
 * 
 * @param string $class_name
 * 
 * @return boolean|int 1 if is valid, 0 OR FALSE if is not valid
 * 
 * @throws \InvalidArgumentException
 */
function isValidClassName($class_name) {
    
    if( !is_string($class_name) ) {
        
        $msg = 'The expected value for the first argument to '
             . '`' . __FUNCTION__ . '($class_name)` should be a String value.'
             . ' `'. ucfirst(gettype($class_name)). '` with the value below was supplied:'
             . PHP_EOL . var_export($class_name, true).PHP_EOL;
        
        throw new \InvalidArgumentException($msg);
    }
    
    $regex_4_valid_class_name = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    return preg_match( $regex_4_valid_class_name, preg_quote($class_name, '/') );
}

/**
 * 
 * 
 * 
 * @param string $controller_2_extend
 * 
 * @return boolean
 * 
 * @throws \InvalidArgumentException
 */
function isValidExtendsClassName($controller_2_extend) {
    
    if( !is_string($controller_2_extend) ) {
        
        $msg = 'The expected value for the first argument to '
             . '`' . __FUNCTION__ . '($controller_2_extend)` should be a String value.'
             . ' `'. ucfirst(gettype($controller_2_extend)). '` with the value below was supplied:'
             . PHP_EOL . var_export($controller_2_extend, true).PHP_EOL;
        
        throw new \InvalidArgumentException($msg);
    }
    
    $extend_class_parts = explode('\\', $controller_2_extend);

    if( strlen($extend_class_parts[0]) <= 0 ) {

        //the extend class name started with a leading \
        //eg. \SomeNameSpace\ControllerClass
        unset($extend_class_parts[0]);
    }

    foreach($extend_class_parts as $class_part) {

        if( !isValidClassName($class_part) ) { return false; }
    }
    
    return true;
}

/**
 * 
 * 
 * 
 * @param string $namepace_4_controller
 * 
 * @return boolean
 * 
 * @throws \InvalidArgumentException
 */
function isValidNamespaceName($namepace_4_controller) {
    
    if( !is_string($namepace_4_controller) ) {
        
        $msg = 'The expected value for the first argument to '
             . '`' . __FUNCTION__ . '($namepace_4_controller)` should be a String value.'
             . ' `'. ucfirst(gettype($namepace_4_controller)). '` with the value below was supplied:'
             . PHP_EOL . var_export($namepace_4_controller, true).PHP_EOL;
        
        throw new \InvalidArgumentException($msg);
    }
    
    $namespace_parts = explode('\\', $namepace_4_controller);

    if( strlen($namespace_parts[0]) <= 0 ) {

        //the namespace started with a leading \
        //eg. \SomeNameSpace\SubNameSpace
        unset($namespace_parts[0]);
    }

    foreach($namespace_parts as $namespace_part) {

        if( !isValidClassName($namespace_part) ) { return false; }
    }
    
    return true;
}

/**
 * 
 * 
 * 
 * @param string $str
 * 
 * @param boolean $append_new_line
 * 
 * @return void
 * 
 * @throws \InvalidArgumentException
 */
function printError($str, $append_new_line = true) {
    
    if( !is_string($str) ) {
        
        $msg = 'The expected value for the first argument to '
             . '`' . __FUNCTION__ . '($str, $append_new_line = true)` should be a String value.'
             . ' `'. ucfirst(gettype($str)). '` with the value below was supplied:'
             . PHP_EOL . var_export($str, true) . PHP_EOL;
        
        throw new \InvalidArgumentException($msg);
    }
    
    echo \Slim3MvcTools\Functions\Str\color_4_console( "ERROR: $str", "red",  "black");
    
    if( ((bool)$append_new_line) ) { echo PHP_EOL; }
}

/**
 * 
 * 
 * 
 * @param string $str
 * @param boolean $append_new_line
 * 
 * @return void
 * 
 * @throws \InvalidArgumentException
 */
function printInfo($str, $append_new_line = true) {
    
    if( !is_string($str) ) {
        
        $msg = 'The expected value for the first argument to '
             . '`' . __FUNCTION__ . '($str, $append_new_line = true)` should be a String value.'
             . ' `'. ucfirst(gettype($str)). '` with the value below was supplied:'
             . PHP_EOL . var_export($str, true).PHP_EOL;
        
        throw new \InvalidArgumentException($msg);
    }
    
    echo \Slim3MvcTools\Functions\Str\color_4_console( $str, "green",  "black");
    
    if( ((bool)$append_new_line) ) { echo PHP_EOL; }
}

/**
 * 
 *  
 * 
 * @param string $path
 * 
 * @return string
 * 
 * @throws \InvalidArgumentException
 */
function normalizeFolderPathForOs($path) {
    
    if( !is_string($path) ) {
        
        $msg = 'The expected value for the first argument to '
             . '`' . __FUNCTION__ . '($path)` should be a String value.'
             . ' `'. ucfirst(gettype($path)). '` with the value below was supplied:'
             . PHP_EOL . var_export($path, true).PHP_EOL;
        
        throw new \InvalidArgumentException($msg);
    }
    
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
 * 
 * @return int|boolean the number of bytes that were written to the file located at $dest, 
 *                     or FALSE on failure.
 * 
 * @throws \InvalidArgumentException
 */
function processTemplateFile($target, $dest, array $replaces) {
    
    if( !is_string($target) ) {
        
        $msg = 'The expected value for the first argument to '
             . '`' . __FUNCTION__ . '($target, $dest, array $replaces)` should be a String value.'
             . ' `'. ucfirst(gettype($target)). '` with the value below was supplied:'
             . PHP_EOL . var_export($target, true).PHP_EOL;
        
        throw new \InvalidArgumentException($msg);
    }
    
    if( !is_string($dest) ) {
        
        $msg = 'The expected value for the second argument to '
             . '`' . __FUNCTION__ . '($target, $dest, array $replaces)` should be a String value.'
             . ' `'. ucfirst(gettype($dest)). '` with the value below was supplied:'
             . PHP_EOL . var_export($dest, true) . PHP_EOL;
        
        throw new \InvalidArgumentException($msg);
    }
    
    $retval = file_get_contents($target);
    
    if($retval !== false) {

        $file_contents = $retval;
        
        $retval = file_put_contents(
                        $dest,
                        strtr(
                            $file_contents,
                            $replaces
                        )
                    );

        if( $retval !== false ) {

            chmod($dest, 0755);

        } else {
            
            @unlink($dest);
        }
        
    } else {
        
        @unlink($target);
    }
    
    return $retval;
}

/**
 * 
 * 
 * @param int $argc
 * @param array $argv
 * 
 * @return void
 * 
 * @throws \InvalidArgumentException
 * @throws \RuntimeException if this function is called in a script that is not run at the command line.
 */
function createController($argc, array $argv) {
    
    //////////////////////////////////////////
    // START: Environment and Args Validation
    //////////////////////////////////////////
    if( !isPhpRunningInCliMode() ) {
        
        $err_msg = '`' . __FUNCTION__ . '($argc, array $argv)` should only be called from within'
                   . ' php scripts that should be run via the command line!!!'.PHP_EOL;
        throw new \RuntimeException($err_msg);
    }
    
    if( is_string($argc) && is_numeric($argc) ) {
        
        $argc = (int) $argc;
    }
    
    if( !is_int($argc) ) {
        
        $err_msg = 'The expected value for the first argument to '
                   . '`' . __FUNCTION__ . '($argc, array $argv)` should be an int.'
                   . ' `'. ucfirst(gettype($argc)). '` with the value below was supplied:'.PHP_EOL
                   . var_export($argc, true).PHP_EOL.PHP_EOL
                   . 'Good bye!!!';
        throw new \InvalidArgumentException($err_msg);
    }
    
    if( count($argv) < 1 ) {
        
        $err_msg = 'The expected value for the second argument to '
                   . '`' . __FUNCTION__ . '($argc, array $argv)` should be an array with at least one element. Empty Array was supplied.'
                   . 'This second argument is expected to be the $argv array passed by PHP to the script calling this function.';
        throw new \InvalidArgumentException($err_msg);
    }
    //////////////////////////////////////////
    // END: Environment and Args Validation
    //////////////////////////////////////////
    
    //////////////////////////////////
    ///START: COMMAND PROCESSING
    //////////////////////////////////
    $ds = DIRECTORY_SEPARATOR;
    
    if ( 
        $argc < 5
        || in_array('--help', $argv)
        || in_array('-help', $argv)
        || in_array('-h', $argv)
        || in_array('-?', $argv)
    ) {
        displayHelp(basename($argv[0]));

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
            return;
        }

        $src_folder_path = getOptVal('--path-to-src-folder', $argv);

        if($src_folder_path === false) {

            $src_folder_path = getOptVal('-p', $argv);
        }

        $src_folder_path = normalizeFolderPathForOs($src_folder_path);

        if( !file_exists($src_folder_path) || !is_dir($src_folder_path) ) {

            printError("The src folder path `$src_folder_path` supplied is a non-existent directory. Goodbye!!");
            return;
        }

        ////////////////////////////////////////////////////////////////////////////
        $default_controller_2_extend = '\\Slim3MvcTools\\Controllers\\BaseController';

        $controller_2_extend = getOptVal('--extends-controller', $argv);

        if($controller_2_extend === false) {

            $controller_2_extend = getOptVal('-e', $argv);

            if($controller_2_extend !== false) {

                if( !isValidExtendsClassName($controller_2_extend) ) {

                    printError("Invalid controller class name `$controller_2_extend` for extension supplied. Goodbye!!");
                    return;
                }

            } else {

                //use default controller class to be extended
                $controller_2_extend = $default_controller_2_extend;
            }
        } else {

            if( !isValidExtendsClassName($controller_2_extend) ) {

                printError("Invalid controller class name `$controller_2_extend` for extension supplied. Goodbye!!");
                return;
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
                    return;
                }

                //validation passed
                $namepace_declaration = "namespace {$namepace_4_controller};";

            } else {
                $namepace_4_controller = '';
            }
        } else {

            if( !isValidNamespaceName($namepace_4_controller) ) {

                printError("Invalid namespace `$namepace_4_controller` supplied. Goodbye!!");
                return;
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

        $template_controller_file = $templates_dir.'controller-class-template.php.tpl';
        $dest_controller_class_file_folder = $src_folder_path.'controllers'.$ds;
        $dest_controller_class_file = $dest_controller_class_file_folder."{$studly_controller_name}.php";

        if( 
            !file_exists($dest_controller_class_file_folder)
            && !mkdir($dest_controller_class_file_folder, 0775, true)
        ) {
            printError("Failed to create `$dest_controller_class_file_folder`; the folder supposed to contain the controller named `$studly_controller_name`. Goodbye!!");
            return;
        }

        $template_view_file = $templates_dir.'index-view-template.php';
        $dest_view_file_folder = $src_folder_path.'views'.$ds."{$dashed_controller_name}{$ds}";
        $dest_view_file = "{$dest_view_file_folder}index.php";

        if( 
            !file_exists($dest_view_file_folder)
            && !mkdir($dest_view_file_folder, 0775, true) 
        ) {    
            printError("Failed to create `$dest_view_file_folder`; the folder supposed to contain views for the controller named `$studly_controller_name`. Goodbye!!");
            return;
        }

        if( file_exists($dest_controller_class_file) ) {

            printError("Controller class `$studly_controller_name` already exists in `$dest_controller_class_file`. Goodbye!!");
            return;
        }

        if( file_exists($dest_view_file) ) {

            printError("View file `$dest_view_file` already exists for Controller class `$studly_controller_name`. Goodbye!!");
            return;
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

            return;

        } else {

            printInfo("Successfully created `{$dest_view_file}` ....".PHP_EOL);
        }
        
        printInfo("All done!!");
        
        if ( strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        
            //test if composer is avaliable only if server OS on which this script is being run
            //is not windows            
//            if( @isCommandAvailableOnOs('composer') ) {
//                
//                passthru('composer dumpautoload');
//                
//            } else {
                
                printInfo("Remember to run `composer dumpautoload` so that composer can pick up the newly created controller class `$studly_controller_name` in `{$dest_controller_class_file}`.");
//            }
        } else {
            printInfo("Remember to run `composer dumpautoload` so that composer can pick up the newly created controller class `$studly_controller_name` in `{$dest_controller_class_file}`.");
        }

        

        //we are done

    } else {

        displayHelp(basename($argv[0]));
    }
    //////////////////////////////////
    ///END: COMMAND PROCESSING
    //////////////////////////////////
}

function isPhpRunningInCliMode() 
{
    return php_sapi_name() === 'cli';
}

function isCommandAvailableOnOs($command) {
    
    $output = [];

    exec( 'command -v '.$command.' >& /dev/null && echo "Found" || echo "Not Found"', $output );

    if ( $output[0] === "Found" ) {
        // command is available
        return TRUE;
    } else {
        // command is unavailable
        return FALSE;
    }
}