<?php
namespace SlimMvcTools\Functions\CliHelpers;

include_once __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."functions".DIRECTORY_SEPARATOR."str-helpers.php";

interface CliExitCodes {
    public const SUCCESS_EXIT   = 0; // printInfo should be used for related messages
    public const FAILURE_EXIT   = 1; // printError should be used for related messages
    public const EXCEPTION_EXIT = 2; // printError should be used for related messages
}

class CreateControllerReturnValue {
    
    /**
     * Must be any one of 
     *  CliExitCodes::SUCCESS_EXIT, 
     *  CliExitCodes::EXCEPTION_EXIT or
     *  CliExitCodes::FAILURE_EXIT
     */
    private int $returnCode;
    
    /**
     * Description of the operation just performed by createController
     */
    private string $returnMessage;
    
    public function __construct(
        int $returnCode,
        string $returnMessage
    ) {
        $this->returnCode = $returnCode;
        $this->returnMessage = $returnMessage;
    }
    
    /** 
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getReturnCode(): int {
        
        return $this->returnCode;
    }
    
    /** 
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getReturnMessage(): string {
        
        return $this->returnMessage;
    }
}
    

function displayHelp(string $cur_script): string {
    
    return <<<HELP
This is a script intended for creating a controller class and a default index view file in rotexsoft/slim-skeleton-mvc-app derived projects.

Usage:
  php {$cur_script} [options]

Example:
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which by default extends `\SlimMvcTools\Controllers\BaseController`)  and a default view in `src/views/foo-bar/index.php`

    php {$cur_script} -c foo-bar -p "/var/www/html/my-app/src"

    php {$cur_script} --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src"

# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which extends `\SomeNameSpace\Controller2Extend`) and a default view in `src/views/foo-bar/index.php`

    php {$cur_script} -c foo-bar -p "/var/www/html/my-app/src" -e "\\SomeNameSpace\\Controller2Extend"

    php {$cur_script} --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src" --extends-controller "\\SomeNameSpace\\Controller2Extend"

Options:
  -h, -?, -help, --help         Display this help message

  -c, --controller-name         The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar. This option REQUIRES at least the `-p` or `--path-to-src-folder` option to work.

  -e, --extends-controller      The name of the controller class (optionally including the name-space prefix) that you want your created controller to extend. `\\SlimMvcTools\\Controllers\\BaseController` is the default value if this option is not specified. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is the correct full class name). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.

  -n, --namespace-4-controller  The name of the namespace the new controller will belong to. If omitted the namespace declaration will not be present in the new controller class. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is a valid name for a php namespace). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.

  -p, --path-to-src-folder      The absolute path to the `src` folder. Eg. `/var/www/html/my-app/src`. This option REQUIRES at least the `-c` (or `--controller-name`) option to work.

HELP;
}

/**
 * @param string[] $args a non-associative array of strings [ 'key1', 'val1', .... 'keyN', 'valN'].
 *                    Values with even indices are the keys while values with odd indices are the
 *                    values.
 *                    For example, $args[0] is the key to $args[1], $args[2] is the key to $args[3], etc.
 */
function getOptVal(string $opt, array $args): ?string {

    $search_key = $opt;

    $opts_index = array_search($search_key, $args, true);
    
    if($opts_index === false) { // option doesn't exist

        return null;
    }
    
    /** 
     * @psalm-suppress InvalidOperand
     * @psalm-suppress MixedArrayOffset
     */
    if(!isset($args[++$opts_index]) ) { // option didn't have a corresponding value

        return '';
    }

    /** @psalm-suppress MixedArrayOffset */
    return $args[$opts_index];
}

/**
 * @return boolean true if valid OR false if not valid
 */
function isValidClassName(string $class_name): bool {

    $regex_4_valid_class_name = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    return (bool)preg_match( $regex_4_valid_class_name, preg_quote($class_name, '/') );
}

function isValidExtendsClassName(string $controller_2_extend): bool {
    
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

function isValidNamespaceName(string $namepace_4_controller): bool {

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

function printError(string $str, bool $append_new_line = true): void {

    echo \SlimMvcTools\Functions\Str\color_4_console( "ERROR: {$str}", "red",  "black");

    if( $append_new_line ) { echo PHP_EOL; }
}

function printInfo(string $str, bool $append_new_line = true): void {

    echo \SlimMvcTools\Functions\Str\color_4_console( $str, "green",  "black");

    if( $append_new_line ) { echo PHP_EOL; }
}

function printType(int $print_type, string $str, bool $append_new_line = true) : void {
    
    if($print_type === CliExitCodes::SUCCESS_EXIT) {
        
        printInfo($str, $append_new_line);
        
    } else {
        
        printError($str, $append_new_line);
    }
}

function normalizeNameSpaceName(string $namespace_name): string {

    if(strlen($namespace_name) > 1 && $namespace_name[0] === '\\') {

        //strip off the preceding \
        $namespace_name = substr($namespace_name, 1);
    }

    return $namespace_name;
}

function normalizeFolderPathForOs(string $path): string {
    
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
 */
function processTemplateFile(string $target, string $dest, array $replaces) {
    
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
 * @param int|string $argc number of arguments
 * @param string[] $argv
 *
 * @throws \InvalidArgumentException
 * @throws \RuntimeException if this function is called in a script that is not run at the command line.
 * 
 * @psalm-suppress RiskyTruthyFalsyComparison
 */
function createController($argc, array $argv): CreateControllerReturnValue {

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

        $err_msg = 'The expected value for the first argument to `' 
                   . __FUNCTION__ . '($argc, array $argv)` should be an int.'
                   . ' `'. ucfirst(gettype($argc)). '` with the value below was supplied:'.PHP_EOL
                   . var_export($argc, true).PHP_EOL.PHP_EOL
                   . 'Good bye!!!';
        throw new \InvalidArgumentException($err_msg);
    }

    if( count($argv) < 1 ) {

        $err_msg = 'The expected value for the second argument to `' 
                   . __FUNCTION__ . '($argc, array $argv)` should be an array with at least one element. Empty Array was supplied.'
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
        in_array('--help', $argv)
        || in_array('-help', $argv)
        || in_array('-h', $argv)
        || in_array('-?', $argv)
        || $argc < 5
    ) {
        return new CreateControllerReturnValue(
            CliExitCodes::SUCCESS_EXIT,
            displayHelp(basename($argv[0])) 
        );

    } else {

        if (
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
            $controller_name = getOptVal('--controller-name', $argv) ?: (getOptVal('-c', $argv) ?: '');
            $studly_controller_name = \SlimMvcTools\Functions\Str\dashesToStudly($controller_name);
            $dashed_controller_name = mb_strtolower($controller_name, 'UTF-8');

            if( !isValidClassName($studly_controller_name) ) {

                return new CreateControllerReturnValue(
                    CliExitCodes::FAILURE_EXIT,
                    "Invalid controller class name `$controller_name` supplied. Goodbye!!"
                );
            }

            $src_folder_path = normalizeFolderPathForOs(
                getOptVal('--path-to-src-folder', $argv)
                    ?: (getOptVal('-p', $argv) ?: '')
            );

            if( !is_dir($src_folder_path) ) {

                return new CreateControllerReturnValue(
                    CliExitCodes::FAILURE_EXIT,
                    "The src folder path `$src_folder_path` supplied is a non-existent directory. Goodbye!!"
                );
            }

            ////////////////////////////////////////////////////////////////////////////
            $default_controller_2_extend = '\\' . \SlimMvcTools\Controllers\BaseController::class;

            $controller_2_extend = getOptVal('--extends-controller', $argv)
                                        ?: (getOptVal('-e', $argv) ?: '');

            if ($controller_2_extend !== '') {

                if( !isValidExtendsClassName($controller_2_extend) ) {

                    return new CreateControllerReturnValue(
                        CliExitCodes::FAILURE_EXIT,
                        "Invalid controller class name `$controller_2_extend` for extension supplied. Goodbye!!"
                    );
                }

            } else {

                //use default controller class to be extended
                $controller_2_extend = $default_controller_2_extend;
            }


            ////////////////////////////////////////////////////////////////////////////
            $namepace_declaration = '';
            //omit namespace declaration by default
            $namepace_4_controller = 
                getOptVal('--namespace-4-controller', $argv) ?: (getOptVal('-n', $argv) ?: '');

            if($namepace_4_controller !== '') {

                if( !isValidNamespaceName($namepace_4_controller) ) {

                    return new CreateControllerReturnValue(
                        CliExitCodes::FAILURE_EXIT,
                        "Invalid namespace `$namepace_4_controller` supplied. Goodbye!!"
                    );
                }

                //validation passed
                $namepace_4_controller = normalizeNameSpaceName($namepace_4_controller);
                $namepace_declaration = "namespace {$namepace_4_controller};";
            }

            //read template controller and substitute __TEMPLTATE_CONTROLLER__ with given controller name \SlimMvcTools\Functions\Str\dashesToStudly($controller_name_from_cli)
            //write processed controller file to SMVC_APP_ROOT_PATH.$ds.'src'.$ds.'controllers'.$ds

            //make the dir SMVC_APP_ROOT_PATH.$ds.'src'.$ds.'views'.$ds.\SlimMvcTools\Functions\Str\toDashes($controller_name_from_cli)
            //read template controller index view and substitute __TEMPLTATE_CONTROLLER__ with given controller name \SlimMvcTools\Functions\Str\dashesToStudly($controller_name_from_cli)
            //write processed controller file to SMVC_APP_ROOT_PATH.$ds.'src'.$ds.'views'.$ds.\SlimMvcTools\Functions\Str\toDashes($controller_name_from_cli)

            $template_controller_file = $templates_dir.'controller-class-template.php.tpl';
            $dest_controller_class_file_folder = $src_folder_path.'controllers'.$ds;
            $dest_controller_class_file = $dest_controller_class_file_folder."{$studly_controller_name}.php";

            if(
                !file_exists($dest_controller_class_file_folder)
                && !mkdir($dest_controller_class_file_folder, 0775, true)
            ) {
                return new CreateControllerReturnValue(
                    CliExitCodes::FAILURE_EXIT,
                    "Failed to create `$dest_controller_class_file_folder`; the folder supposed to contain the controller named `$studly_controller_name`. Goodbye!!"
                );
            }

            $template_view_file = $templates_dir.'index-view-template.php';
            $dest_view_file_folder = $src_folder_path.'views'.$ds."{$dashed_controller_name}{$ds}";
            $dest_view_file = "{$dest_view_file_folder}index.php";

            if(
                !file_exists($dest_view_file_folder)
                && !mkdir($dest_view_file_folder, 0775, true)
            ) {
                return new CreateControllerReturnValue(
                    CliExitCodes::FAILURE_EXIT,
                    "Failed to create `$dest_view_file_folder`; the folder supposed to contain views for the controller named `$studly_controller_name`. Goodbye!!"
                );
            }

            if( file_exists($dest_controller_class_file) ) {

                return new CreateControllerReturnValue(
                    CliExitCodes::FAILURE_EXIT,
                    "Controller class `$studly_controller_name` already exists in `$dest_controller_class_file`. Goodbye!!"
                );
            }

            if( file_exists($dest_view_file) ) {

                return new CreateControllerReturnValue(
                    CliExitCodes::FAILURE_EXIT,
                    "View file `$dest_view_file` already exists for Controller class `$studly_controller_name`. Goodbye!!"
                );
            }

            $success_messages = "Creating Controller Class `$studly_controller_name` in `{$dest_controller_class_file}` ....";

            ////////////////////////////////////////////////////////////////////////////
            $replaces = [
                '__CONTROLLER_2_EXTEND__' => $controller_2_extend,
                '__TEMPLTATE_CONTROLLER__' => $studly_controller_name,
                'namespace __NAMESPACE_2_REPLACE__;' => $namepace_declaration,
                "'__login_success_redirect_controller__'" => "'{$dashed_controller_name}'",
            ];

            if( processTemplateFile($template_controller_file, $dest_controller_class_file, $replaces) === false ) {

                return new CreateControllerReturnValue(
                    CliExitCodes::FAILURE_EXIT,
                    "Failed transforming template controller `$template_controller_file` to `$dest_controller_class_file`. Goodbye!!"
                );

            } else {

                $success_messages .= PHP_EOL . "Successfully created `{$dest_controller_class_file}` ....".PHP_EOL;
            }

            $success_messages .= PHP_EOL . "Creating index view for `{$studly_controller_name}::actionIndex()` in `{$dest_view_file}` ....";

            $replaces['__TEMPLTATE_CONTROLLER__'] = rtrim($namepace_4_controller, '\\').'\\'.$studly_controller_name;

            if( processTemplateFile($template_view_file, $dest_view_file, $replaces) === false ) {

                $error_messages = "Failed creating index view for `{$studly_controller_name}::actionIndex()` in `{$dest_view_file}`.";
                $error_messages .= PHP_EOL ."Deleting `{$dest_controller_class_file}` ....";

                if( !unlink($dest_controller_class_file) ) {

                    $error_messages .= PHP_EOL . "Failed to delete `{$dest_controller_class_file}`. Please delete it manually.";
                }

                $error_messages .= PHP_EOL . "Goodbye!!";

                return new CreateControllerReturnValue(
                    CliExitCodes::FAILURE_EXIT,
                    $error_messages
                );

            } else {

                $success_messages .= PHP_EOL . "Successfully created `{$dest_view_file}` ....".PHP_EOL;
            }

            $success_messages .= PHP_EOL . "All done!!";
            $success_messages .= PHP_EOL . "Remember to run `composer dumpautoload` so that composer can pick up the newly created controller class `$studly_controller_name` in `{$dest_controller_class_file}`.";

            return new CreateControllerReturnValue(
                CliExitCodes::SUCCESS_EXIT, 
                $success_messages
            );

        } else {
            /** @psalm-suppress MixedArgument */
            return new CreateControllerReturnValue(
                CliExitCodes::FAILURE_EXIT,
                'Incorrect arguments / parameters were supplied. Please run '
                 . PHP_EOL . PHP_EOL . basename($argv[0]) . ' -h' . PHP_EOL
                 . PHP_EOL . 'for the details on how to properly run '
                 . basename($argv[0])
            );
        }
    }
    //////////////////////////////////
    ///END: COMMAND PROCESSING
    //////////////////////////////////
}

function isPhpRunningInCliMode(): bool {
    
    return php_sapi_name() === 'cli';
}
