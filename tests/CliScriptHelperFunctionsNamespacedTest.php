<?php
declare(strict_types=1);
namespace SlimMvcTools\Functions\CliHelpers;

$ds = DIRECTORY_SEPARATOR;
include_once __DIR__."{$ds}..{$ds}src{$ds}scripts{$ds}cli-script-helper-functions.php";

/**
 * This Test Class is name-spaced under \SlimMvcTools\Functions\CliHelpers to allow
 * for the overriding of some built-in php functions like php_sapi_name() in order
 * to better test some functions in ./src/scripts/cli-script-helper-functions.php
 *
 * @author rotimi
 */
class CliScriptHelperFunctionsNamespacedTest extends \PHPUnit\Framework\TestCase {

    protected function setUp():void { parent::setUp(); }
    
    public function testThatCreateControllerThrowsExceptionWhenIsPhpRunningInCliModeReturnsFalse() {
                
        try {
            // php_sapi_name
            $builder = new \phpmock\MockBuilder();
            $builder->setNamespace(__NAMESPACE__)
                    ->setName("php_sapi_name")
                    ->setFunction(
                        function () {
                            return 'non-cli';
                        }
                    );
            $mock = $builder->build();
            $mock->enable();
                    
            \SlimMvcTools\Functions\CliHelpers\createController(1, ['an arg']);
            $this->fail(\RuntimeException::class . ' was not thrown');
            
        } catch (\RuntimeException $exc) {

            $expected_msg = '($argc, array $argv)` should only be called from within'
                      . ' php scripts that should be run via the command line!!!';
            
            self::assertStringContainsString($expected_msg, $exc->getMessage());
            
        } finally {
            
            if(isset($mock) && $mock instanceof \phpmock\Mock) {
                
                $mock->disable();
            }
        }
    }
    
    public function testThatCreateControllerThrowsExceptionWhenArgcIsNotAnIntOrString() {
                
        try {
            $argc = new \ArrayObject([777]);
            \SlimMvcTools\Functions\CliHelpers\createController($argc, ['an arg']);
            $this->fail(\InvalidArgumentException::class . ' was not thrown');
            
        } catch (\InvalidArgumentException $exc) {
            
            self::assertStringContainsString(
                'The expected value for the first argument to `', 
                $exc->getMessage()
            );
            self::assertStringContainsString(
                '($argc, array $argv)` should be an int.', 
                $exc->getMessage()
            );
            self::assertStringContainsString(
                ' `'. ucfirst(gettype($argc)). '` with the value below was supplied:'.PHP_EOL, 
                $exc->getMessage()
            );
            self::assertStringContainsString(
                var_export($argc, true).PHP_EOL.PHP_EOL, 
                $exc->getMessage()
            );
            self::assertStringContainsString(
                'Good bye!!!', 
                $exc->getMessage()
            );
        }
    }
    
    public function testThatCreateControllerThrowsExceptionWhenArgvIsEmpty() {
                
        try {
            \SlimMvcTools\Functions\CliHelpers\createController(1, []);
            $this->fail(\InvalidArgumentException::class . ' was not thrown');
            
        } catch (\InvalidArgumentException $exc) {

            self::assertStringContainsString(
                'The expected value for the second argument to `', 
                $exc->getMessage()
            );
            self::assertStringContainsString(
                '($argc, array $argv)` should be an array with at least one element. Empty Array was supplied.', 
                $exc->getMessage()
            );
            self::assertStringContainsString(
                'This second argument is expected to be the $argv array passed by PHP to the script calling this function.', 
                $exc->getMessage()
            );
        }
    }
    
    public function testThatCreateControllerWorksAsExpectedWhenDestinationControllerClassFolderCannotBeCreated() {
        
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'blog-posts',
                '--path-to-src-folder', __DIR__,
                '--extends-controller', \SlimMvcTools\Controllers\BaseController::class,
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'blog-posts',
                '-p', __DIR__,
                '-e', \SlimMvcTools\Controllers\BaseController::class,
            ],
        ];
        
        $builder = new \phpmock\MockBuilder();
        $builder->setNamespace(__NAMESPACE__)
                ->setName("file_exists")
                ->setFunction(
                    function (string $filename): bool {
                        return str_contains($filename, 'controllers') ? false : \file_exists($filename);
                    }
                );

        $mock = $builder->build();
        $mock->enable();
        
        $builder2 = new \phpmock\MockBuilder();
        $builder2->setNamespace(__NAMESPACE__)
                ->setName("mkdir")
                ->setFunction(
                    function (
                        string $directory,
                        int $permissions = 0777,
                        bool $recursive = false,
                        $context = null
                    ): bool {
                        return str_contains($directory, 'controllers') ? false : \mkdir($directory, $permissions, $recursive, $context);
                    }
                );

        $mock2 = $builder2->build();
        $mock2->enable();
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            
            $expected_message = 'Failed to create `' . __DIR__ . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR
                    . '`; the folder supposed to contain the controller named `BlogPosts`. Goodbye!!';
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
            self::assertEquals($expected_message, $return_val->getReturnMessage());
        }
        
        $mock->disable();
        $mock2->disable();
    }
    
    public function testThatCreateControllerWorksAsExpectedWhenDestinationViewFilesFoldersCannotBeCreated() {
        
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'blog-posts',
                '--path-to-src-folder', __DIR__,
                '--extends-controller', \SlimMvcTools\Controllers\BaseController::class,
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'blog-posts',
                '-p', __DIR__,
                '-e', \SlimMvcTools\Controllers\BaseController::class,
            ],
        ];
        
        $builder = new \phpmock\MockBuilder();
        $builder->setNamespace(__NAMESPACE__)
                ->setName("file_exists")
                ->setFunction(
                    function (string $filename): bool {
                        return str_contains($filename, 'views') ? false : \file_exists($filename);
                    }
                );

        $mock = $builder->build();
        $mock->enable();
        
        $builder2 = new \phpmock\MockBuilder();
        $builder2->setNamespace(__NAMESPACE__)
                ->setName("mkdir")
                ->setFunction(
                    function (
                        string $directory,
                        int $permissions = 0777,
                        bool $recursive = false,
                        $context = null
                    ): bool {
                        return str_contains($directory, 'views') ? false : \mkdir($directory, $permissions, $recursive, $context);
                    }
                );

        $mock2 = $builder2->build();
        $mock2->enable();
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            
            $expected_message = 'Failed to create `' . __DIR__ . DIRECTORY_SEPARATOR . 'views' 
                    . DIRECTORY_SEPARATOR . 'blog-posts' . DIRECTORY_SEPARATOR
                    . '`; the folder supposed to contain views for the controller named `BlogPosts`. Goodbye!!';
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
            self::assertEquals($expected_message, $return_val->getReturnMessage());
        }
        
        $mock->disable();
        $mock2->disable();
    }
    
    public function testThatCreateControllerWorksAsExpectedWhenDestinationControllerClassExists() {
        
        $src_path = SMVC_APP_ROOT_PATH . DIRECTORY_SEPARATOR . 'src';
        $dest_controller_class_file = $src_path . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'ChildController.php';
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'child-controller',
                '--path-to-src-folder', $src_path,
                '--extends-controller', \SlimMvcTools\Controllers\BaseController::class,
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'child-controller',
                '-p', $src_path,
                '-e', \SlimMvcTools\Controllers\BaseController::class,
            ],
        ];
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            
            $expected_message = "Controller class `ChildController` already exists in `{$dest_controller_class_file}`. Goodbye!!";
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
            self::assertEquals($expected_message, $return_val->getReturnMessage());
        }
    }
    
    public function testThatCreateControllerWorksAsExpectedWhenDestinationViewIndexDotPhpFileExists() {
        
        $src_path = SMVC_APP_ROOT_PATH . DIRECTORY_SEPARATOR . 'src';
        $dest_view_file = 
            $src_path . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR 
                      . 'controller-with-no-controller-class' . DIRECTORY_SEPARATOR . 'index.php';
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'controller-with-no-controller-class',
                '--path-to-src-folder', $src_path,
                '--extends-controller', \SlimMvcTools\Controllers\BaseController::class,
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'controller-with-no-controller-class',
                '-p', $src_path,
                '-e', \SlimMvcTools\Controllers\BaseController::class,
            ],
        ];
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            
            $expected_message = "View file `$dest_view_file` already exists for Controller class `ControllerWithNoControllerClass`. Goodbye!!";
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
            self::assertEquals($expected_message, $return_val->getReturnMessage());
        }
    }
}
