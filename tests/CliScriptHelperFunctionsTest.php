<?php
$ds = DIRECTORY_SEPARATOR;
include __DIR__."{$ds}..{$ds}src{$ds}scripts{$ds}cli-script-helper-functions.php";

/**
 * This class tests \slim3-skeleton-mvc-tools\src\scripts\cli-script-helper-functions.php
 *
 * @author Rotimi Adegbamigbe
 */
class CliScriptHelperFunctionsTest extends \PHPUnit\Framework\TestCase
{
    protected $ds; //directory separator
    
    protected function setUp():void {
        
        parent::setUp();
        $this->ds = DIRECTORY_SEPARATOR;
    }

    public function testThatDisplayHelpWorksAsExpected() {
        
        $output = $this->execFuncAndReturnBufferedOutput('displayHelp', ['smvc-create-controller']);
        
        $expected_substr = <<<INPUT
This is a script intended for creating a controller class and a default index view file in rotexsoft/slim3-skeleton-mvc-app derived projects.

Usage:
  php smvc-create-controller [options]

Example:
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which by default extends `\Slim3MvcTools\Controllers\BaseController`)  and a default view in `src/views/foo-bar/index.php`
    
    php smvc-create-controller -c foo-bar -p "/var/www/html/my-app/src"
    
    php smvc-create-controller --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src"
  
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which extends `\SomeNameSpace\Controller2Extend`) and a default view in `src/views/foo-bar/index.php`
  
    php smvc-create-controller -c foo-bar -p "/var/www/html/my-app/src" -e "\SomeNameSpace\Controller2Extend"
    
    php smvc-create-controller --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src" --extends-controller "\SomeNameSpace\Controller2Extend"

Options:
  -h, -?, -help, --help         Display this help message
    
  -c, --controller-name         The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar. This option REQUIRES at least the `-p` or `--path-to-src-folder` option to work.
  
  -e, --extends-controller      The name of the controller class (optionally including the name-space prefix) that you want your created controller to extend. `\\Slim3MvcTools\\Controllers\\BaseController` is the default value if this option is not specified. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is the correct full class name). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.
    
  -n, --namespace-4-controller  The name of the namespace the new controller will belong to. If omitted the namespace declaration will not be present in the new controller class. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is a valid name for a php namespace). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.
    
  -p, --path-to-src-folder      The absolute path to the `src` folder. Eg. `/var/www/html/my-app/src`. This option REQUIRES at least the `-c` (or `--controller-name`) option to work.
INPUT;
        $this->assertStringContainsString($expected_substr, $output);
        
        ///////////////////////////////////////////
        //Test \InvalidArgumentException messages 
        ///////////////////////////////////////////
        $args = array (
            'Integer' => 111,
            'Double' => 111.1234,
            'Boolean' => true,
            'Array' => [],
            'Object' => (new stdclass()),
            'NULL' => null,
            'Resource' => tmpfile(),
            //'Object' => $func , //doesn't seem to work as an array value
        );
        $function_sig_in_err_msg = 'displayHelp($cur_script)';
            
        foreach($args as $arg_type => $arg) {
            
            try {
                displayHelp($arg);
                $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
                static::fail($msg);

            } catch(\InvalidArgumentException $e) {

                $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                            . " should be a String value. `{$arg_type}` with the value below was supplied:";
                $this->assertStringContainsString($msg_substr, $e->getMessage());
            }
        }
        
        // test the callable type
        try {
            displayHelp(function() { echo 'blah'; });
            $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
            static::fail($msg);

        } catch(\InvalidArgumentException $e) {

            $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                        . " should be a String value. `Object` with the value below was supplied:";
            $this->assertStringContainsString($msg_substr, $e->getMessage());
        }
    }
    
    public function testThatPrintErrorWorksAsExpected() {
        
        $output1 = $this->execFuncAndReturnBufferedOutput('printError', ['test string', false]);
        $output2 = $this->execFuncAndReturnBufferedOutput('printError', ['test string', true]);
        
        $this->assertStringStartsWith("\033[0;31m\033[40m", $output1);
        $this->assertStringStartsWith("\033[0;31m\033[40m", $output2);
        $this->assertStringEndsWith("\033[0m", $output1);
        $this->assertStringEndsWith(PHP_EOL, $output2);
        
        ///////////////////////////////////////////
        //Test \InvalidArgumentException messages 
        ///////////////////////////////////////////
        $args = array (
            'Integer' => 111,
            'Double' => 111.1234,
            'Boolean' => true,
            'Array' => [],
            'Object' => (new stdclass()),
            'NULL' => null,
            'Resource' => tmpfile(),
            //'Object' => $func , //doesn't seem to work as an array value
        );
        $function_sig_in_err_msg = 'printError($str, $append_new_line = true)';
            
        foreach($args as $arg_type => $arg) {
            
            try {
                printError($arg);
                $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
                static::fail($msg);

            } catch(\InvalidArgumentException $e) {

                $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                            . " should be a String value. `{$arg_type}` with the value below was supplied:";
                $this->assertStringContainsString($msg_substr, $e->getMessage());
            }
        }
        
        // test the callable type
        try {
            printError(function() { echo 'blah'; });
            $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
            static::fail($msg);

        } catch(\InvalidArgumentException $e) {

            $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                        . " should be a String value. `Object` with the value below was supplied:";
            $this->assertStringContainsString($msg_substr, $e->getMessage());
        }
    }

    public function testThatPrintInfoWorksAsExpected() {
                
        $output1 = $this->execFuncAndReturnBufferedOutput('printInfo', ['test string', false]);
        $output2 = $this->execFuncAndReturnBufferedOutput('printInfo', ['test string', true]);
        
        $this->assertStringStartsWith("\033[0;32m\033[40m", $output1);
        $this->assertStringStartsWith("\033[0;32m\033[40m", $output2);
        $this->assertStringEndsWith("\033[0m", $output1);
        $this->assertStringEndsWith(PHP_EOL, $output2);
        
        ///////////////////////////////////////////
        //Test \InvalidArgumentException messages 
        ///////////////////////////////////////////
        $args = array (
            'Integer' => 111,
            'Double' => 111.1234,
            'Boolean' => true,
            'Array' => [],
            'Object' => (new stdclass()),
            'NULL' => null,
            'Resource' => tmpfile(),
            //'Object' => $func , //doesn't seem to work as an array value
        );
        $function_sig_in_err_msg = 'printInfo($str, $append_new_line = true)';
            
        foreach($args as $arg_type => $arg) {
            
            try {
                printInfo($arg);
                $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
                static::fail($msg);

            } catch(\InvalidArgumentException $e) {

                $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                            . " should be a String value. `{$arg_type}` with the value below was supplied:";
                $this->assertStringContainsString($msg_substr, $e->getMessage());
            }
        }
        
        // test the callable type
        try {
            printInfo(function() { echo 'blah'; });
            $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
            static::fail($msg);

        } catch(\InvalidArgumentException $e) {

            $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                        . " should be a String value. `Object` with the value below was supplied:";
            $this->assertStringContainsString($msg_substr, $e->getMessage());
        }
    }

    public function testThatGetOptValWorksAsExpected() {

        $opts_vals = [
                        '-n', 'n`s-val', 
                        '-c', 'c`s-val', 
                        '-e', 'e`s-val', 
                        '-d'
                    ];

        $this->assertEquals('c`s-val', getOptVal('-c', $opts_vals));
        $this->assertEquals('e`s-val', getOptVal('-e', $opts_vals));
        $this->assertEquals('n`s-val', getOptVal('-n', $opts_vals));
        $this->assertEquals(false, getOptVal('-d', $opts_vals));
        $this->assertEquals(false, getOptVal('-x', $opts_vals));
        
        ///////////////////////////////////////////
        //Test \InvalidArgumentException messages 
        ///////////////////////////////////////////
        $args = array (
            //'Integer' => 111, //not applicable to this test
            //'Double' => 111.1234, //not applicable to this test
            'Boolean' => true,
            'Array' => [],
            'Object' => (new stdclass()),
            'NULL' => null,
            'Resource' => tmpfile(),
            //'Object' => $func , //doesn't seem to work as an array value
        );
        $function_sig_in_err_msg = 'getOptVal($opt, array $args)';
            
        foreach($args as $arg_type => $arg) {
            
            try {
                getOptVal($arg, []);
                $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
                static::fail($msg);

            } catch(\InvalidArgumentException $e) {

                $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                            . " should be a String value. `{$arg_type}` with the value below was supplied:";
                $this->assertStringContainsString($msg_substr, $e->getMessage());
            }
        }
        
        // test the callable type
        try {
            getOptVal(function() { echo 'blah'; }, []);
            $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
            static::fail($msg);

        } catch(\InvalidArgumentException $e) {

            $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                        . " should be a String value. `Object` with the value below was supplied:";
            $this->assertStringContainsString($msg_substr, $e->getMessage());
        }
    }

    public function testThatNormalizeFolderPathForOsWorksAsExpected() {

        $path1 = __DIR__.'/';
        $path2 = __DIR__.'\\';
        $expected_normalized_path = __DIR__.DIRECTORY_SEPARATOR;
        $this->assertEquals($expected_normalized_path, normalizeFolderPathForOs($path1));
        $this->assertEquals($expected_normalized_path, normalizeFolderPathForOs($path2));
        
        ///////////////////////////////////////////
        //Test \InvalidArgumentException messages 
        ///////////////////////////////////////////
        $args = array (
            'Integer' => 111,
            'Double' => 111.1234,
            'Boolean' => true,
            'Array' => [],
            'Object' => (new stdclass()),
            'NULL' => null,
            'Resource' => tmpfile(),
            //'Object' => $func , //doesn't seem to work as an array value
        );
        $function_sig_in_err_msg = 'normalizeFolderPathForOs($path)';
            
        foreach($args as $arg_type => $arg) {
            
            try {
                normalizeFolderPathForOs($arg);
                $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
                static::fail($msg);

            } catch(\InvalidArgumentException $e) {

                $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                            . " should be a String value. `{$arg_type}` with the value below was supplied:";
                $this->assertStringContainsString($msg_substr, $e->getMessage());
            }
        }
        
        // test the callable type
        try {
            normalizeFolderPathForOs(function() { echo 'blah'; });
            $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
            static::fail($msg);

        } catch(\InvalidArgumentException $e) {

            $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                        . " should be a String value. `Object` with the value below was supplied:";
            $this->assertStringContainsString($msg_substr, $e->getMessage());
        }
    }
    
    public function testThatIsValidNamespaceNameWorksAsExpected() {

        $this->assertEquals(true, isValidNamespaceName('Name\\Space'));
        $this->assertEquals(true, isValidNamespaceName('\\Name\\Space'));
        $this->assertEquals(false, isValidNamespaceName('Name\\Space\\'));
        $this->assertEquals(false, isValidNamespaceName('\\Name\\Space\\'));
        $this->assertEquals(false, isValidNamespaceName('-Name\\Space'));
        $this->assertEquals(false, isValidNamespaceName('Name\\-Space'));
        
        ///////////////////////////////////////////
        //Test \InvalidArgumentException messages 
        ///////////////////////////////////////////
        $args = array (
            'Integer' => 111,
            'Double' => 111.1234,
            'Boolean' => true,
            'Array' => [],
            'Object' => (new stdclass()),
            'NULL' => null,
            'Resource' => tmpfile(),
            //'Object' => $func , //doesn't seem to work as an array value
        );
        $function_sig_in_err_msg = 'isValidNamespaceName($namepace_4_controller)';
            
        foreach($args as $arg_type => $arg) {
            
            try {
                isValidNamespaceName($arg);
                $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
                static::fail($msg);

            } catch(\InvalidArgumentException $e) {

                $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                            . " should be a String value. `{$arg_type}` with the value below was supplied:";
                $this->assertStringContainsString($msg_substr, $e->getMessage());
            }
        }
        
        // test the callable type
        try {
            isValidNamespaceName(function() { echo 'blah'; });
            $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
            static::fail($msg);

        } catch(\InvalidArgumentException $e) {

            $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                        . " should be a String value. `Object` with the value below was supplied:";
            $this->assertStringContainsString($msg_substr, $e->getMessage());
        }
    }
    
    public function testThatIsValidExtendsClassNameWorksAsExpected() {

        $this->assertEquals(true, isValidExtendsClassName('Name\\Space\\Class'));
        $this->assertEquals(true, isValidExtendsClassName('\\Name\\Space\\Class'));
        $this->assertEquals(false, isValidExtendsClassName('NameSpace\\Class\\'));
        $this->assertEquals(false, isValidExtendsClassName('\\NameSpace\\Class\\'));
        $this->assertEquals(false, isValidExtendsClassName('-NameSpace\\Class'));
        $this->assertEquals(false, isValidExtendsClassName('NameSpace\\-Class'));
        
        ///////////////////////////////////////////
        //Test \InvalidArgumentException messages 
        ///////////////////////////////////////////
        $args = array (
            'Integer' => 111,
            'Double' => 111.1234,
            'Boolean' => true,
            'Array' => [],
            'Object' => (new stdclass()),
            'NULL' => null,
            'Resource' => tmpfile(),
            //'Object' => $func , //doesn't seem to work as an array value
        );
        $function_sig_in_err_msg = 'isValidExtendsClassName($controller_2_extend)';
            
        foreach($args as $arg_type => $arg) {
            
            try {
                isValidExtendsClassName($arg);
                $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
                static::fail($msg);

            } catch(\InvalidArgumentException $e) {

                $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                            . " should be a String value. `{$arg_type}` with the value below was supplied:";
                $this->assertStringContainsString($msg_substr, $e->getMessage());
            }
        }
        
        // test the callable type
        try {
            isValidExtendsClassName(function() { echo 'blah'; });
            $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
            static::fail($msg);

        } catch(\InvalidArgumentException $e) {

            $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                        . " should be a String value. `Object` with the value below was supplied:";
            $this->assertStringContainsString($msg_substr, $e->getMessage());
        }
    }
    
    public function testThatIsValidClassNameWorksAsExpected() {

        $this->assertEquals(true, isValidClassName('SomeClass'));
        $this->assertEquals(true, isValidClassName('Some1Class'));
        $this->assertEquals(true, isValidClassName('SomeClass1'));
        $this->assertEquals(true, isValidClassName('Some_Class'));
        $this->assertEquals(true, isValidClassName('Some1_Class'));
        $this->assertEquals(true, isValidClassName('Some_1Class'));
        $this->assertEquals(true, isValidClassName('Some_Class1'));
        
        $this->assertEquals(false, isValidClassName('1SomeClass'));
        $this->assertEquals(false, isValidClassName('1Some_Class'));
        $this->assertEquals(false, isValidClassName('Some\\Class\\'));
        $this->assertEquals(false, isValidClassName('Some-Class'));
        $this->assertEquals(false, isValidClassName('-SomeClass'));
        $this->assertEquals(false, isValidClassName('SomeClass-'));
        
        ///////////////////////////////////////////
        //Test \InvalidArgumentException messages 
        ///////////////////////////////////////////
        $args = array (
            'Integer' => 111,
            'Double' => 111.1234,
            'Boolean' => true,
            'Array' => [],
            'Object' => (new stdclass()),
            'NULL' => null,
            'Resource' => tmpfile(),
            //'Object' => $func , //doesn't seem to work as an array value
        );
        $function_sig_in_err_msg = 'isValidClassName($class_name)';
            
        foreach($args as $arg_type => $arg) {
            
            try {
                isValidClassName($arg);
                $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
                static::fail($msg);

            } catch(\InvalidArgumentException $e) {

                $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                            . " should be a String value. `{$arg_type}` with the value below was supplied:";
                $this->assertStringContainsString($msg_substr, $e->getMessage());
            }
        }
        
        // test the callable type
        try {
            isValidClassName(function() { echo 'blah'; });
            $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
            static::fail($msg);

        } catch(\InvalidArgumentException $e) {

            $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                        . " should be a String value. `Object` with the value below was supplied:";
            $this->assertStringContainsString($msg_substr, $e->getMessage());
        }
    }

    public function testThatProcessTemplateFileWorksAsExpected() {

        $replaces = [
            '__CONTROLLER_2_EXTEND__' => "SomeNameSpace\\Controller2Extend",
            '__TEMPLTATE_CONTROLLER__' => "FooBar",
            'namespace __NAMESPACE_2_REPLACE__;' => "namespace Test\\Space;",
            "'__login_success_redirect_controller__'" => "'foo-bar'",
        ];
        
        //Make sure processTemplateFile does not return false when the file is valid
        $template_controller_file = dirname(__DIR__).  $this->ds .'src'.  $this->ds . 'templates' . $this->ds .'controller-class-template.php.tpl';
        $dest_controller_class_file = __DIR__.  $this->ds . 'test-template-output' . $this->ds .'FooBar.php';
        $this->assertNotEquals(false, processTemplateFile($template_controller_file, $dest_controller_class_file, $replaces));
        
        //Make sure processTemplateFile does not return false when the file is valid
        $replaces['__TEMPLTATE_CONTROLLER__'] = "Test\\Space\\FooBar";
        $template_view_file = dirname(__DIR__).  $this->ds .'src'.  $this->ds . 'templates' . $this->ds .'index-view-template.php';
        $dest_view_file = __DIR__.  $this->ds . 'test-template-output' . $this->ds .'index.php';
        $this->assertNotEquals(false, processTemplateFile($template_view_file, $dest_view_file, $replaces));
        
        //Make sure processTemplateFile returns false when any of the files is non existent
        $this->assertEquals(false, @processTemplateFile('bad file', $dest_view_file, $replaces));
        $this->assertEquals(false, @processTemplateFile('bad file3', 'bad file4', $replaces));
        
        //false when there is no perm to write destination file (Works in Linux)
        $this->assertEquals(false, @processTemplateFile($template_view_file, '/root/bad-perm-file.txt', $replaces));
        
        ////////////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////
        //Ensure the processed controller file template contains the expected output
        $expected_controller_class_file = __DIR__ . $this->ds . 'test-template-output' . $this->ds .'ExpectedFooBar.php';
        $expected_controller_file_contents_as_str = file_get_contents($expected_controller_class_file);
        $controller_file_contents_as_str = file_get_contents($dest_controller_class_file);
        $this->assertEquals($expected_controller_file_contents_as_str, $controller_file_contents_as_str);
        
        //Ensure the processed view file template contains the expected output
        $expected_view_file = __DIR__ . $this->ds . 'test-template-output' . $this->ds .'expected-index.php';
        $expected_view_file_contents_as_str = file_get_contents($expected_view_file);
        $view_file_contents_as_str = file_get_contents($dest_view_file);
        $this->assertEquals($expected_view_file_contents_as_str, $view_file_contents_as_str);

        //check file perms: should be 775
        $this->assertEquals("100755", sprintf('%o', fileperms($dest_controller_class_file)));
        $this->assertEquals("100755", sprintf('%o', fileperms($dest_view_file)));

        //clean up
        unlink($dest_controller_class_file);
        unlink($dest_view_file);
        
        ///////////////////////////////////////////
        //Test \InvalidArgumentException messages 
        ///////////////////////////////////////////
        $args = array (
            'Integer' => 111,
            'Double' => 111.1234,
            'Boolean' => true,
            'Array' => [],
            'Object' => (new stdclass()),
            'NULL' => null,
            'Resource' => tmpfile(),
            //'Object' => $func , //doesn't seem to work as an array value
        );
        $function_sig_in_err_msg = 'processTemplateFile($target, $dest, array $replaces)';
            
        foreach($args as $arg_type => $arg) {
            
            try {
                processTemplateFile($arg, '', $replaces);
                $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
                static::fail($msg);

            } catch(\InvalidArgumentException $e) {

                $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                            . " should be a String value. `{$arg_type}` with the value below was supplied:";
                $this->assertStringContainsString($msg_substr, $e->getMessage());
            }
        }
        
        // test the callable type
        try {
            processTemplateFile(function() { echo 'blah'; }, '', $replaces);
            $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
            static::fail($msg);

        } catch(\InvalidArgumentException $e) {

            $msg_substr = "The expected value for the first argument to `$function_sig_in_err_msg`"
                        . " should be a String value. `Object` with the value below was supplied:";
            $this->assertStringContainsString($msg_substr, $e->getMessage());
        }
            
        foreach($args as $arg_type => $arg) {
            
            try {
                processTemplateFile('', $arg, $replaces);
                $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
                static::fail($msg);

            } catch(\InvalidArgumentException $e) {

                $msg_substr = "The expected value for the second argument to `$function_sig_in_err_msg`"
                            . " should be a String value. `{$arg_type}` with the value below was supplied:";
                $this->assertStringContainsString($msg_substr, $e->getMessage());
            }
        }
        
        // test the callable type
        try {
            processTemplateFile('', function() { echo 'blah'; }, $replaces);
            $msg = '\InvalidArgumentException should have been thrown in `' . __FILE__ . '`' . ' on line '. (__LINE__ - 1) ;
            static::fail($msg);

        } catch(\InvalidArgumentException $e) {

            $msg_substr = "The expected value for the second argument to `$function_sig_in_err_msg`"
                        . " should be a String value. `Object` with the value below was supplied:";
            $this->assertStringContainsString($msg_substr, $e->getMessage());
        }
    }
    
    public function testThatCreateControllerScriptWorksAsExpectedWithValidArgsAndValidArgVals() {
        
        $expected_output_showing_help_page = <<<INPUT
This is a script intended for creating a controller class and a default index view file in rotexsoft/slim3-skeleton-mvc-app derived projects.

Usage:
  php smvc-create-controller [options]

Example:
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which by default extends `\Slim3MvcTools\Controllers\BaseController`)  and a default view in `src/views/foo-bar/index.php`
    
    php smvc-create-controller -c foo-bar -p "/var/www/html/my-app/src"
    
    php smvc-create-controller --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src"
  
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which extends `\SomeNameSpace\Controller2Extend`) and a default view in `src/views/foo-bar/index.php`
  
    php smvc-create-controller -c foo-bar -p "/var/www/html/my-app/src" -e "\SomeNameSpace\Controller2Extend"
    
    php smvc-create-controller --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src" --extends-controller "\SomeNameSpace\Controller2Extend"

Options:
  -h, -?, -help, --help         Display this help message
    
  -c, --controller-name         The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar. This option REQUIRES at least the `-p` or `--path-to-src-folder` option to work.
  
  -e, --extends-controller      The name of the controller class (optionally including the name-space prefix) that you want your created controller to extend. `\\Slim3MvcTools\\Controllers\\BaseController` is the default value if this option is not specified. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is the correct full class name). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.
    
  -n, --namespace-4-controller  The name of the namespace the new controller will belong to. If omitted the namespace declaration will not be present in the new controller class. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is a valid name for a php namespace). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.
    
  -p, --path-to-src-folder      The absolute path to the `src` folder. Eg. `/var/www/html/my-app/src`. This option REQUIRES at least the `-c` (or `--controller-name`) option to work.
INPUT;
        //createController(1, ['smvc-create-controller']);
        $argc = 1;
        $argv = ['smvc-create-controller']; //script name is always at index 0
        $captured_script_output = $this->execFuncAndReturnBufferedOutput('createController', [$argc, $argv], true);

        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
/*
        //run script with -? arg
        $captured_script_output = `php {$this->script_2_test} -?`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with -h arg
        $captured_script_output = `php {$this->script_2_test} -h`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with -help arg
        $captured_script_output = `php {$this->script_2_test} -help`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with --help arg
        $captured_script_output = `php {$this->script_2_test} --help`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with -c arg
        $captured_script_output = `php {$this->script_2_test} -c`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with -c arg with value
        $captured_script_output = `php {$this->script_2_test} -c SomeController`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with --controller-name arg
        $captured_script_output = `php {$this->script_2_test} --controller-name`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with --controller-name arg with value
        $captured_script_output = `php {$this->script_2_test} --controller-name SomeController`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with -p arg
        $captured_script_output = `php {$this->script_2_test} -p`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with -p arg with value
        $captured_script_output = `php {$this->script_2_test} -p /path/to/your/apps/source-files`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with --path-to-src-folder arg
        $captured_script_output = `php {$this->script_2_test} --path-to-src-folder`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with --path-to-src-folder arg with value
        $captured_script_output = `php {$this->script_2_test} --path-to-src-folder /path/to/your/apps/source-files`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
                
        //run script with -e arg
        $captured_script_output = `php {$this->script_2_test} -e`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with -e arg with value
        $captured_script_output = `php {$this->script_2_test} -e SomeController2Extend`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with --extends-controller arg
        $captured_script_output = `php {$this->script_2_test} --extends-controller`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with --extends-controller arg with value
        $captured_script_output = `php {$this->script_2_test} --extends-controller SomeController2Extend`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
                
        //run script with -n arg
        $captured_script_output = `php {$this->script_2_test} -n`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with -n arg with value
        $captured_script_output = `php {$this->script_2_test} -n SomeNameSpace\ForNewController`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with --namespace-4-controller arg
        $captured_script_output = `php {$this->script_2_test} --namespace-4-controller`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
        
        //run script with --namespace-4-controller arg with value
        $captured_script_output = `php {$this->script_2_test} --namespace-4-controller SomeNameSpace\ForNewController`;
        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
*/
    }
    
    protected function execFuncAndReturnBufferedOutput($func_name, array $args=[], $strip_bin_markers=false) {
        
        // Capture the output
        ob_start();
        
        call_user_func_array($func_name, $args);
        
        // Get the captured output and close the buffer and return the captured output
        return ($strip_bin_markers) 
                ? str_replace(["\033[0;31m\033[40m", "\033[0;32m\033[40m", "\033[0m"], ['', '', ''], ob_get_clean())  
                : ob_get_clean();
    }
}
