<?php
$ds = DIRECTORY_SEPARATOR;
include_once __DIR__."{$ds}..{$ds}src{$ds}scripts{$ds}cli-script-helper-functions.php";

/**
 * This class tests .\src\scripts\cli-script-helper-functions.php
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
        
        $output = \SlimMvcTools\Functions\CliHelpers\displayHelp('smvc-create-controller');

        $expected_substr = <<<INPUT
This is a script intended for creating a controller class and a default index view file in rotexsoft/slim-skeleton-mvc-app derived projects.

Usage:
  php smvc-create-controller [options]

Example:
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which by default extends `\SlimMvcTools\Controllers\BaseController`)  and a default view in `src/views/foo-bar/index.php`

    php smvc-create-controller -c foo-bar -p "/var/www/html/my-app/src"

    php smvc-create-controller --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src"

# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which extends `\SomeNameSpace\Controller2Extend`) and a default view in `src/views/foo-bar/index.php`

    php smvc-create-controller -c foo-bar -p "/var/www/html/my-app/src" -e "\SomeNameSpace\Controller2Extend"

    php smvc-create-controller --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src" --extends-controller "\SomeNameSpace\Controller2Extend"

Options:
  -h, -?, -help, --help         Display this help message

  -c, --controller-name         The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar. This option REQUIRES at least the `-p` or `--path-to-src-folder` option to work.

  -e, --extends-controller      The name of the controller class (optionally including the name-space prefix) that you want your created controller to extend. `\\SlimMvcTools\\Controllers\\BaseController` is the default value if this option is not specified. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is the correct full class name). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.

  -n, --namespace-4-controller  The name of the namespace the new controller will belong to. If omitted the namespace declaration will not be present in the new controller class. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is a valid name for a php namespace). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.

  -p, --path-to-src-folder      The absolute path to the `src` folder. Eg. `/var/www/html/my-app/src`. This option REQUIRES at least the `-c` (or `--controller-name`) option to work.
INPUT;
        self::assertStringContainsString($expected_substr, $output);
    }

    public function testThatPrintErrorWorksAsExpected() {

        $output1 = $this->execFuncAndReturnBufferedOutput('\\SlimMvcTools\\Functions\\CliHelpers\\printError', ['test string', false]);
        $output2 = $this->execFuncAndReturnBufferedOutput('\\SlimMvcTools\\Functions\\CliHelpers\\printError', ['test string', true]);

        self::assertStringStartsWith("\033[0;31m\033[40m", $output1);
        self::assertStringStartsWith("\033[0;31m\033[40m", $output2);
        self::assertStringEndsWith("\033[0m", $output1);
        self::assertStringEndsWith(PHP_EOL, $output2);
    }

    public function testThatPrintInfoWorksAsExpected() {

        $output1 = $this->execFuncAndReturnBufferedOutput('\\SlimMvcTools\\Functions\\CliHelpers\\printInfo', ['test string', false]);
        $output2 = $this->execFuncAndReturnBufferedOutput('\\SlimMvcTools\\Functions\\CliHelpers\\printInfo', ['test string', true]);

        self::assertStringStartsWith("\033[0;32m\033[40m", $output1);
        self::assertStringStartsWith("\033[0;32m\033[40m", $output2);
        self::assertStringEndsWith("\033[0m", $output1);
        self::assertStringEndsWith(PHP_EOL, $output2);
    }

    public function testThatPrintTypeWorksAsExpected() {

        $output1 = $this->execFuncAndReturnBufferedOutput(
            '\\SlimMvcTools\\Functions\\CliHelpers\\printType', 
            [\SlimMvcTools\Functions\CliHelpers\CliExitCodes::SUCCESS_EXIT, 'test string', false]
        );
        $output2 = $this->execFuncAndReturnBufferedOutput(
            '\\SlimMvcTools\\Functions\\CliHelpers\\printType', 
            [\SlimMvcTools\Functions\CliHelpers\CliExitCodes::SUCCESS_EXIT, 'test string', true]
        );

        self::assertStringStartsWith("\033[0;32m\033[40m", $output1);
        self::assertStringStartsWith("\033[0;32m\033[40m", $output2);
        self::assertStringEndsWith("\033[0m", $output1);
        self::assertStringEndsWith(PHP_EOL, $output2);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $output3 = $this->execFuncAndReturnBufferedOutput(
            '\\SlimMvcTools\\Functions\\CliHelpers\\printType', 
            [\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, 'test string', false]
        );
        $output4 = $this->execFuncAndReturnBufferedOutput(
            '\\SlimMvcTools\\Functions\\CliHelpers\\printType', 
            [\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, 'test string', true]
        );

        self::assertStringStartsWith("\033[0;31m\033[40m", $output3);
        self::assertStringStartsWith("\033[0;31m\033[40m", $output4);
        self::assertStringEndsWith("\033[0m", $output3);
        self::assertStringEndsWith(PHP_EOL, $output4);
    }

    public function testThatGetOptValWorksAsExpected() {

        $opts_vals = [
                        '-n', 'n`s-val',
                        '-c', 'c`s-val',
                        '-e', 'e`s-val',
                        '-d'
                    ];

        self::assertEquals('c`s-val', \SlimMvcTools\Functions\CliHelpers\getOptVal('-c', $opts_vals));
        self::assertEquals('e`s-val', \SlimMvcTools\Functions\CliHelpers\getOptVal('-e', $opts_vals));
        self::assertEquals('n`s-val', \SlimMvcTools\Functions\CliHelpers\getOptVal('-n', $opts_vals));
        self::assertEquals('', \SlimMvcTools\Functions\CliHelpers\getOptVal('-d', $opts_vals)); // option without a corresponding value
        self::assertNull(\SlimMvcTools\Functions\CliHelpers\getOptVal('-x', $opts_vals)); // option doesn't exist
    }

    public function testThatNormalizeFolderPathForOsWorksAsExpected() {

        $path1 = __DIR__.'/';
        $path2 = __DIR__.'\\';
        $expected_normalized_path = __DIR__.DIRECTORY_SEPARATOR;
        self::assertEquals($expected_normalized_path, \SlimMvcTools\Functions\CliHelpers\normalizeFolderPathForOs($path1));
        self::assertEquals($expected_normalized_path, \SlimMvcTools\Functions\CliHelpers\normalizeFolderPathForOs($path2));
    }

    public function testThatNormalizeNameSpaceNameWorksAsExpected() {
        
        $ns1 = '\\Name\\Space\\Class';
        $ns2 = 'Name\\Space\\Class';
        $expected_normalized_ns = 'Name\\Space\\Class';
        self::assertEquals($expected_normalized_ns, \SlimMvcTools\Functions\CliHelpers\normalizeNameSpaceName($ns1));
        self::assertEquals($expected_normalized_ns, \SlimMvcTools\Functions\CliHelpers\normalizeNameSpaceName($ns2));
    }

    public function testThatIsValidNamespaceNameWorksAsExpected() {

        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidNamespaceName('Name\\Space'));
        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidNamespaceName('\\Name\\Space'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidNamespaceName('Name\\Space\\'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidNamespaceName('\\Name\\Space\\'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidNamespaceName('-Name\\Space'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidNamespaceName('Name\\-Space'));
    }

    public function testThatIsValidExtendsClassNameWorksAsExpected() {

        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidExtendsClassName('SMVCTools\\Tests\\TestObjects\\ChildController'));
        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidExtendsClassName('\\SMVCTools\\Tests\\TestObjects\\ChildController'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidExtendsClassName('SMVCTools\\Tests\\TestObjects\\NonController'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidExtendsClassName('\\SMVCTools\\Tests\\TestObjects\\NonController'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidExtendsClassName('NameSpace\\Class\\'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidExtendsClassName('\\NameSpace\\Class\\'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidExtendsClassName('-NameSpace\\Class'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidExtendsClassName('NameSpace\\-Class'));
    }

    public function testThatIsValidClassNameWorksAsExpected() {

        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidClassName('SomeClass'));
        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidClassName('Some1Class'));
        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidClassName('SomeClass1'));
        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidClassName('Some_Class'));
        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidClassName('Some1_Class'));
        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidClassName('Some_1Class'));
        self::assertTrue(\SlimMvcTools\Functions\CliHelpers\isValidClassName('Some_Class1'));

        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidClassName('1SomeClass'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidClassName('1Some_Class'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidClassName('Some\\Class\\'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidClassName('Some-Class'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidClassName('-SomeClass'));
        self::assertFalse(\SlimMvcTools\Functions\CliHelpers\isValidClassName('SomeClass-'));
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
        self::assertNotFalse(\SlimMvcTools\Functions\CliHelpers\processTemplateFile($template_controller_file, $dest_controller_class_file, $replaces));

        //Make sure processTemplateFile does not return false when the file is valid
        $replaces['__TEMPLTATE_CONTROLLER__'] = "Test\\Space\\FooBar";
        $template_view_file = dirname(__DIR__).  $this->ds .'src'.  $this->ds . 'templates' . $this->ds .'index-view-template.php';
        $dest_view_file = __DIR__.  $this->ds . 'test-template-output' . $this->ds .'index.php';
        self::assertNotFalse(\SlimMvcTools\Functions\CliHelpers\processTemplateFile($template_view_file, $dest_view_file, $replaces));

        //Make sure processTemplateFile returns false when any of the files is non existent
        self::assertFalse(@\SlimMvcTools\Functions\CliHelpers\processTemplateFile('bad file', $dest_view_file, $replaces));
        self::assertFalse(@\SlimMvcTools\Functions\CliHelpers\processTemplateFile('bad file3', 'bad file4', $replaces));

        //false when there is no perm to write destination file (Works in Linux)
        self::assertFalse(@\SlimMvcTools\Functions\CliHelpers\processTemplateFile($template_view_file, '/root/bad-perm-file.txt', $replaces));

        ////////////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////
        //Ensure the processed controller file template contains the expected output
        $expected_controller_class_file = __DIR__ . $this->ds . 'test-template-output' . $this->ds .'ExpectedFooBar.php';
        $expected_controller_file_contents_as_str = file_get_contents($expected_controller_class_file);
        $controller_file_contents_as_str = file_get_contents($dest_controller_class_file);
        self::assertEquals($expected_controller_file_contents_as_str, $controller_file_contents_as_str);

        //Ensure the processed view file template contains the expected output
        $expected_view_file = __DIR__ . $this->ds . 'test-template-output' . $this->ds .'expected-index.php';
        $expected_view_file_contents_as_str = file_get_contents($expected_view_file);
        $view_file_contents_as_str = file_get_contents($dest_view_file);
        self::assertEquals($expected_view_file_contents_as_str, $view_file_contents_as_str);

        //check file perms: should be 775
        self::assertEquals("100755", sprintf('%o', fileperms($dest_controller_class_file)));
        self::assertEquals("100755", sprintf('%o', fileperms($dest_view_file)));

        //clean up
        unlink($dest_controller_class_file);
        unlink($dest_view_file);
    }

    public function testThatCreateControllerScriptWorksAsExpectedWithInvalidControllerClassName() {
        
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', '-1bad-comtroller-name',
                '--path-to-src-folder', './src',
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', '-1bad-comtroller-name',
                '-p', './src',
            ],
            
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', '-1bad-comtroller-name',
                '--path-to-src-folder', './src',
                '--extends-controller', \SlimMvcTools\Controllers\BaseController::class,
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', '-1bad-comtroller-name',
                '-p', './src',
                '-e', \SlimMvcTools\Controllers\BaseController::class,
            ],
        ];
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            $expected_message = 'Invalid controller class name `-1bad-comtroller-name` supplied. Goodbye!!';
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
            self::assertEquals($expected_message, $return_val->getReturnMessage());
        }
    }

    public function testThatCreateControllerScriptWorksAsExpectedWithInvalidPathToSrcFolder() {
        
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'blog-posts',
                '--path-to-src-folder', '/non-existent/src/path',
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'blog-posts',
                '-p', '/non-existent/src/path',
            ],
            
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'blog-posts',
                '--path-to-src-folder', '/non-existent/src/path',
                '--extends-controller', \SlimMvcTools\Controllers\BaseController::class,
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'blog-posts',
                '-p', '/non-existent/src/path',
                '-e', \SlimMvcTools\Controllers\BaseController::class,
            ],
        ];
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            $expected_message = 'The src folder path `/non-existent/src/path/` supplied is a non-existent directory. Goodbye!!';
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
            self::assertEquals($expected_message, $return_val->getReturnMessage());
        }
    }

    public function testThatCreateControllerScriptWorksAsExpectedWithInvalidControllerToExtend() {
        
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'blog-posts',
                '--path-to-src-folder', __DIR__,
                '--extends-controller', '1BadControllerToExtendsClassName',
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'blog-posts',
                '-p', __DIR__,
                '-e', '1BadControllerToExtendsClassName',
            ],
        ];
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            $expected_message = "Invalid controller class name `1BadControllerToExtendsClassName` for extension supplied. The class to extend must be `"
                                . \SlimMvcTools\Controllers\BaseController::class ."` or its sub-class. Goodbye!!";
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
            self::assertEquals($expected_message, $return_val->getReturnMessage());
        }
    }

    public function testThatCreateControllerScriptWorksAsExpectedWithInvalidNamespaceName() {
        
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'blog-posts',
                '--path-to-src-folder', __DIR__,
                '--extends-controller', \SlimMvcTools\Controllers\BaseController::class,
                '--namespace-4-controller', '1BadNameSpaceName',
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'blog-posts',
                '-p', __DIR__,
                '-e', \SlimMvcTools\Controllers\BaseController::class,
                '-n', '1BadNameSpaceName',
            ],
        ];
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            $expected_message = 'Invalid namespace `1BadNameSpaceName` supplied. Goodbye!!';
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
            self::assertEquals($expected_message, $return_val->getReturnMessage());
        }
    }

    public function testThatCreateControllerScriptWorksAsExpectedWith5ArgsAndMissingExpectedFlags() {
        
        $argv = [
            'smvc-create-controller', //script name is always at index 0
            '--unknown-flag-1',
            '--unknown-flag-2',
            '--unknown-flag-3',
            '--unknown-flag-4',
        ];
        $this->callAndAssertCreateControllerWitArgvContainingUnknownFlags($argv);
    }

    public function testThatCreateControllerScriptWorksAsExpectedWith7ArgsAndMissingExpectedFlags() {
        
        $argv = [
            'smvc-create-controller', //script name is always at index 0
            '--unknown-flag-1',
            '--unknown-flag-2',
            '--unknown-flag-3',
            '--unknown-flag-4',
            '--unknown-flag-5',
            '--unknown-flag-6',
        ];
        $this->callAndAssertCreateControllerWitArgvContainingUnknownFlags($argv);
    }

    public function testThatCreateControllerScriptWorksAsExpectedWithMoreThan7ArgsAndMissingExpectedFlags() {
        
        $argv = [
            'smvc-create-controller', //script name is always at index 0
            '--unknown-flag-1',
            '--unknown-flag-2',
            '--unknown-flag-3',
            '--unknown-flag-4',
            '--unknown-flag-5',
            '--unknown-flag-6',
            '--unknown-flag-7',
        ];
        $this->callAndAssertCreateControllerWitArgvContainingUnknownFlags($argv);
    }
    
    protected function callAndAssertCreateControllerWitArgvContainingUnknownFlags(array $argv): void {
        
        $argc = count($argv);
        $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
        
        $expected_message = 'Incorrect arguments / parameters were supplied. Please run '
            . PHP_EOL . PHP_EOL . basename($argv[0]) . ' -h' . PHP_EOL
            . PHP_EOL . 'for the details on how to properly run smvc-create-controller';
        
         self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
         self::assertEquals($expected_message, $return_val->getReturnMessage());
    }


    public function testThatCreateControllerWorksAsExpectedWhenLessThan5ArgsOrHelpRelatedFlagsArePassedToIt() {

        $expected_output_showing_help_page = <<<INPUT
This is a script intended for creating a controller class and a default index view file in rotexsoft/slim-skeleton-mvc-app derived projects.

Usage:
  php smvc-create-controller [options]

Example:
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which by default extends `\SlimMvcTools\Controllers\BaseController`)  and a default view in `src/views/foo-bar/index.php`

    php smvc-create-controller -c foo-bar -p "/var/www/html/my-app/src"

    php smvc-create-controller --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src"

# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which extends `\SomeNameSpace\Controller2Extend`) and a default view in `src/views/foo-bar/index.php`

    php smvc-create-controller -c foo-bar -p "/var/www/html/my-app/src" -e "\SomeNameSpace\Controller2Extend"

    php smvc-create-controller --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src" --extends-controller "\SomeNameSpace\Controller2Extend"

Options:
  -h, -?, -help, --help         Display this help message

  -c, --controller-name         The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar. This option REQUIRES at least the `-p` or `--path-to-src-folder` option to work.

  -e, --extends-controller      The name of the controller class (optionally including the name-space prefix) that you want your created controller to extend. `\\SlimMvcTools\\Controllers\\BaseController` is the default value if this option is not specified. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is the correct full class name). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.

  -n, --namespace-4-controller  The name of the namespace the new controller will belong to. If omitted the namespace declaration will not be present in the new controller class. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is a valid name for a php namespace). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.

  -p, --path-to-src-folder      The absolute path to the `src` folder. Eg. `/var/www/html/my-app/src`. This option REQUIRES at least the `-c` (or `--controller-name`) option to work.
INPUT;
        $argvs = [
            ['smvc-create-controller'], //script name is always at index 0
            ['smvc-create-controller', '--help'], //script name is always at index 0
            ['smvc-create-controller', '-help'], //script name is always at index 0
            ['smvc-create-controller', '-h'], //script name is always at index 0
            ['smvc-create-controller', '-?'], //script name is always at index 0
        ];
        
        foreach($argvs as $argv) {
        
            /////////////////
            // pure int argc
            /////////////////
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            
            /////////////////
            // stringy argc
            /////////////////
            $return_val2 = \SlimMvcTools\Functions\CliHelpers\createController($argc . '', $argv);

            self::assertStringContainsString($expected_output_showing_help_page, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_output_showing_help_page, $return_val2->getReturnMessage());
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::SUCCESS_EXIT, $return_val->getReturnCode());
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::SUCCESS_EXIT, $return_val2->getReturnCode());
        }
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
