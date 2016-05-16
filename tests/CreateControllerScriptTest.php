<?php

/**
 * This class tests \slim3-skeleton-mvc-tools\src\scripts\create-controller.php
 *
 * @author Rotimi Adegbamigbe
 */
class CreateControllerScriptTest extends \PHPUnit_Framework_TestCase
{
    protected $ds; //directory separator
    
    protected $script_2_test;

    protected function setUp() {
        
        parent::setUp();
        $ds = DIRECTORY_SEPARATOR;
        $this->ds = DIRECTORY_SEPARATOR;
        $this->script_2_test = __DIR__."{$ds}..{$ds}src{$ds}scripts{$ds}create-controller.php";
    }

    public function testScriptWithHelpArgs() {
        
        // Capture the output
        ob_start();

        displayHelp('create-controller.php');

        // Get the captured output and close the buffer
        $output = ob_get_clean();
        
        $expected_substr = <<<INPUT
This is a script intended for creating a controller class and a default index view file in rotexsoft/slim3-skeleton-mvc-app derived projects.

Usage:
  php create-controller.php [options]

Example:
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which by default extends `\Slim3MvcTools\Controllers\BaseController`)  and a default view in `src/views/foo-bar/index.php`
    
    php create-controller.php -c foo-bar -p "/var/www/html/my-app/src"
    
    php create-controller.php --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src"
  
# either of the commands below will create a controller with the class named `FooBar` in `src/controllers/FooBar.php` (which extends `\SomeNameSpace\Controller2Extend`) and a default view in `src/views/foo-bar/index.php`
  
    php create-controller.php -c foo-bar -p "/var/www/html/my-app/src" -e "\SomeNameSpace\Controller2Extend"
    
    php create-controller.php --controller-name foo-bar --path-to-src-folder "/var/www/html/my-app/src" --extends-controller "\SomeNameSpace\Controller2Extend"

Options:
  -h, -?, -help, --help         Display this help message
    
  -c, --controller-name         The name of the controller class you want to create. The name will be converted to Studly case eg. foo-bar will be changed to FooBar.
  
  -e, --extends-controller      The name of the controller class (optionally including the name-space prefix) that you want your created controller to extend. `\Slim3MvcTools\Controllers\BaseController` is the default value if this option is not specified. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is the correct full class name).
    
  -n, --namespace-4-controller  The name of the namespace the new controller will belong to. If omitted the namespace declaration will not be present in the new controller class. Unlike the value supplied for `--controller-name`, the value supplied for this option will not be converted to Studly case (make sure the value is a valid name for a php namespace).
    
  -p, --path-to-src-folder      The absolute path to the `src` folder. Eg. /var/www/html/my-app/src
INPUT;
        $this->assertContains($expected_substr, $output);
    }


    public function testThatExceptionIsThrownWhenNonStringFileNameIsPassedToConstructor() {

        /**
         * @expectedException InvalidArgumentException
         */
        /*
        $invalid_file_name = array();        
        $renderer = new FileRendererWrapper($invalid_file_name);
         */
    }

    public function testExceptionMessageWhenNonExistentPropertyThatsNeverBeenSetIsAccessed() {
        
        /*
        $expected_msg = "ERROR: Item with key 'key1' does not exist in ";
        $renderer = new FileRendererWrapper('file.txt');
        
        try {
            $renderer->__get('key1'); //explicit call to a property that isn't defined & hasn't been set
            
            //if the call to __get above did not throw an exception, then this test should fail
            $message = __FUNCTION__. '() : Expected exception not thrown when accessing non'
                                   . ' existent property.';
            throw new \Exception($message);
            
        } catch (\Exception $e) {
            
            //echo PHP_EOL.$e->getMessage().PHP_EOL.'yoooo'.PHP_EOL;
            $this->assertContains($expected_msg, $e->getMessage());
            
            try {
                $renderer->key1; //Object access to a property that isn't defined & hasn't been set
                                 //will internally trigger $renderer->__get('key1')

                //if the statement above ($renderer->key1) did not throw an exception, 
                //then this test should fail
                $message = __FUNCTION__. '() : Expected exception not thrown when accessing non'
                                       . ' existent property.';
                throw new \Exception($message);

            } catch (\Exception $e) {

                //echo PHP_EOL.$e->getMessage().PHP_EOL.'yoooo'.PHP_EOL;
                $this->assertContains($expected_msg, $e->getMessage());
            }
        }
        */
    }
}
