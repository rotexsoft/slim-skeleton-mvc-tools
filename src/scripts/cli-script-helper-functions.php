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
    
  -c, --controller-name         The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar.
  
  -e, --extends-controller      The name of the controller class (optionally including the name-space prefix) that you want your created controller to extend. `\\Slim3MvcTools\\Controllers\\BaseController` is the default value if this option is not specified. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is the correct full class name).
    
  -n, --namespace-4-controller  The name of the namespace the new controller will belong to. If omitted the namespace declaration will not be present in the new controller class. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is a valid name for a php namespace).
    
  -p, --path-to-src-folder      The absolute path to the `src` folder. Eg. /var/www/html/my-app/src
 
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
