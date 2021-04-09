<?php

/**
 * This class tests .\src\scripts\smvc-create-controller
 *
 * @author Rotimi Adegbamigbe
 */
class CreateControllerScriptTest extends \PHPUnit\Framework\TestCase
{
    protected $ds; //directory separator

    protected $script_2_test;

    protected function setUp(): void {

        parent::setUp();
        $ds = DIRECTORY_SEPARATOR;
        $this->ds = DIRECTORY_SEPARATOR;
        $this->script_2_test = __DIR__."{$ds}..{$ds}src{$ds}scripts{$ds}smvc-create-controller";
    }

    public function testBecauseThereShouldBeAtLeastOneTestInThisClass() {

        $this->assertTrue(true);
    }

//    public function testThatCreateControllerScriptWorksAsExpectedWithValidArgsAndValidArgVals() {
///*
//        $expected_output_showing_help_page = <<<INPUT
//This is a script intended for creating a controller class and a default index view file in rotexsoft/slim-skeleton-mvc-app derived projects.
//
//Usage:
//  php smvc-create-controller [options]
//
//Example:
//# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which by default extends `\Slim3MvcTools\Controllers\BaseController`)  and a default view in `src/views/foo-bar/index.php`
//
//    php smvc-create-controller -c foo-bar -p "/var/www/html/my-app/src"
//
//    php smvc-create-controller --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src"
//
//# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which extends `\SomeNameSpace\Controller2Extend`) and a default view in `src/views/foo-bar/index.php`
//
//    php smvc-create-controller -c foo-bar -p "/var/www/html/my-app/src" -e "\SomeNameSpace\Controller2Extend"
//
//    php smvc-create-controller --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src" --extends-controller "\SomeNameSpace\Controller2Extend"
//
//Options:
//  -h, -?, -help, --help         Display this help message
//
//  -c, --controller-name         The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar. This option REQUIRES at least the `-p` or `--path-to-src-folder` option to work.
//
//  -e, --extends-controller      The name of the controller class (optionally including the name-space prefix) that you want your created controller to extend. `\\Slim3MvcTools\\Controllers\\BaseController` is the default value if this option is not specified. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is the correct full class name). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.
//
//  -n, --namespace-4-controller  The name of the namespace the new controller will belong to. If omitted the namespace declaration will not be present in the new controller class. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is a valid name for a php namespace). This option REQUIRES at least the `-c` (or `--controller-name`) and the `-p` (or `--path-to-src-folder`) options to work.
//
//  -p, --path-to-src-folder      The absolute path to the `src` folder. Eg. `/var/www/html/my-app/src`. This option REQUIRES at least the `-c` (or `--controller-name`) option to work.
//INPUT;
//        //run script with no args
//        $captured_script_output = `php {$this->script_2_test}`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -? arg
//        $captured_script_output = `php {$this->script_2_test} -?`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -h arg
//        $captured_script_output = `php {$this->script_2_test} -h`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -help arg
//        $captured_script_output = `php {$this->script_2_test} -help`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with --help arg
//        $captured_script_output = `php {$this->script_2_test} --help`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -c arg
//        $captured_script_output = `php {$this->script_2_test} -c`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -c arg with value
//        $captured_script_output = `php {$this->script_2_test} -c SomeController`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with --controller-name arg
//        $captured_script_output = `php {$this->script_2_test} --controller-name`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with --controller-name arg with value
//        $captured_script_output = `php {$this->script_2_test} --controller-name SomeController`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -p arg
//        $captured_script_output = `php {$this->script_2_test} -p`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -p arg with value
//        $captured_script_output = `php {$this->script_2_test} -p /path/to/your/apps/source-files`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with --path-to-src-folder arg
//        $captured_script_output = `php {$this->script_2_test} --path-to-src-folder`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with --path-to-src-folder arg with value
//        $captured_script_output = `php {$this->script_2_test} --path-to-src-folder /path/to/your/apps/source-files`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -e arg
//        $captured_script_output = `php {$this->script_2_test} -e`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -e arg with value
//        $captured_script_output = `php {$this->script_2_test} -e SomeController2Extend`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with --extends-controller arg
//        $captured_script_output = `php {$this->script_2_test} --extends-controller`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with --extends-controller arg with value
//        $captured_script_output = `php {$this->script_2_test} --extends-controller SomeController2Extend`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -n arg
//        $captured_script_output = `php {$this->script_2_test} -n`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with -n arg with value
//        $captured_script_output = `php {$this->script_2_test} -n SomeNameSpace\ForNewController`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with --namespace-4-controller arg
//        $captured_script_output = `php {$this->script_2_test} --namespace-4-controller`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
//
//        //run script with --namespace-4-controller arg with value
//        $captured_script_output = `php {$this->script_2_test} --namespace-4-controller SomeNameSpace\ForNewController`;
//        $this->assertStringContainsString($expected_output_showing_help_page, $captured_script_output);
// *
// */
//    }
//
//
//    public function testThatExceptionIsThrownWhenNonStringFileNameIsPassedToConstructor() {
//
//        /**
//         * @expectedException InvalidArgumentException
//         */
//        /*
//        $invalid_file_name = array();
//        $renderer = new FileRendererWrapper($invalid_file_name);
//         */
//    }
//
//    public function testExceptionMessageWhenNonExistentPropertyThatsNeverBeenSetIsAccessed() {
//
//        /*
//        $expected_msg = "ERROR: Item with key 'key1' does not exist in ";
//        $renderer = new FileRendererWrapper('file.txt');
//
//        try {
//            $renderer->__get('key1'); //explicit call to a property that isn't defined & hasn't been set
//
//            //if the call to __get above did not throw an exception, then this test should fail
//            $message = __FUNCTION__. '() : Expected exception not thrown when accessing non'
//                                   . ' existent property.';
//            throw new \Exception($message);
//
//        } catch (\Exception $e) {
//
//            //echo PHP_EOL.$e->getMessage().PHP_EOL.'yoooo'.PHP_EOL;
//            $this->assertStringContainsString($expected_msg, $e->getMessage());
//
//            try {
//                $renderer->key1; //Object access to a property that isn't defined & hasn't been set
//                                 //will internally trigger $renderer->__get('key1')
//
//                //if the statement above ($renderer->key1) did not throw an exception,
//                //then this test should fail
//                $message = __FUNCTION__. '() : Expected exception not thrown when accessing non'
//                                       . ' existent property.';
//                throw new \Exception($message);
//
//            } catch (\Exception $e) {
//
//                //echo PHP_EOL.$e->getMessage().PHP_EOL.'yoooo'.PHP_EOL;
//                $this->assertStringContainsString($expected_msg, $e->getMessage());
//            }
//        }
//        */
//    }
}
