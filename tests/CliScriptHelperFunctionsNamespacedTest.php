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
    
    use \FileIoUtilsTrait;

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
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::SUCCESS_EXIT, $return_val->getReturnCode());
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
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::SUCCESS_EXIT, $return_val->getReturnCode());
            self::assertEquals($expected_message, $return_val->getReturnMessage());
        }
    }
    
    public function testThatCreateControllerWorksAsExpectedWhenProcessTemplateFileReturnsFalseWhenCreatingAControllerClassFile() {
        
        $src_path = dirname(SMVC_APP_ROOT_PATH . PHP_EOL) . DIRECTORY_SEPARATOR 
                    . 'test-create-controller-output' . DIRECTORY_SEPARATOR . 'src';
        
        $dest_controller_class_file = 
            $src_path . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR 
                      . 'BlogComments.php';
        
        $template_controller_file = dirname(SMVC_APP_ROOT_PATH, 2) . DIRECTORY_SEPARATOR
            . 'src' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR 
            . 'controller-class-template.php.tpl';
        
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'blog-comments',
                '--path-to-src-folder', $src_path,
                '--extends-controller', \SlimMvcTools\Controllers\BaseController::class,
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'blog-comments',
                '-p', $src_path,
                '-e', \SlimMvcTools\Controllers\BaseController::class,
            ],
        ];
        
        $builder = new \phpmock\MockBuilder();
        $builder->setNamespace(__NAMESPACE__)
                ->setName("file_get_contents")
                ->setFunction(
                    function (
                        string $filename,
                        bool $use_include_path = false,
                        $context = null,
                        int $offset = 0,
                        int $length = 0
                    ) {
                        return str_contains($filename, 'controller-class-template.php.tpl') 
                                ? false 
                                : \file_get_contents($filename, $use_include_path, $context, $offset, $length);
                    }
                );

        $mock = $builder->build();
        $mock->enable();
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            
            $expected_message = "Failed transforming template controller `$template_controller_file` to `$dest_controller_class_file`. Goodbye!!";
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
            self::assertEquals($expected_message, $return_val->getReturnMessage());
            
            // clean-up
            $this->rmdirRecursive($src_path . DIRECTORY_SEPARATOR . 'controllers');
            $this->rmdirRecursive($src_path . DIRECTORY_SEPARATOR . 'views');
        }
        
        $mock->disable();
    }
    
    public function testThatCreateControllerWorksAsExpectedWhenProcessTemplateFileReturnsFalseWhenCreatingAnIndexViewFile() {
        
        $src_path = dirname(SMVC_APP_ROOT_PATH . PHP_EOL) . DIRECTORY_SEPARATOR 
                    . 'test-create-controller-output' . DIRECTORY_SEPARATOR . 'src';
        
        $dest_view_file = 
            $src_path . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR 
                      . 'blog-comments' . DIRECTORY_SEPARATOR . 'index.php';
        
        $dest_controller_class_file = 
            $src_path . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR 
                      . 'BlogComments.php';
        
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'blog-comments',
                '--path-to-src-folder', $src_path,
                '--extends-controller', \SlimMvcTools\Controllers\BaseController::class,
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'blog-comments',
                '-p', $src_path,
                '-e', \SlimMvcTools\Controllers\BaseController::class,
            ],
        ];
        
        $builder = new \phpmock\MockBuilder();
        $builder->setNamespace(__NAMESPACE__)
                ->setName("file_get_contents")
                ->setFunction(
                    function (
                        string $filename,
                        bool $use_include_path = false,
                        $context = null,
                        int $offset = 0,
                        int $length = 0
                    ) {
                        return str_contains($filename, 'index-view-template.php') 
                                ? false 
                                : \file_get_contents($filename, $use_include_path, $context, $offset, $length);
                    }
                );

        $mock = $builder->build();
        $mock->enable();
        
        $builder2 = new \phpmock\MockBuilder();
        $builder2->setNamespace(__NAMESPACE__)
                ->setName("unlink")
                ->setFunction(
                    function (string $filename, $context = null) {
                        return str_contains($filename, 'BlogComments.php') 
                                ? false 
                                : \unlink($filename, $context);
                    }
                );

        $mock2 = $builder2->build();
        $mock2->enable();
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            
            $expected_message = "Failed creating index view for `BlogComments::actionIndex()` in `{$dest_view_file}`.";
            $expected_message2 = "Deleting `{$dest_controller_class_file}` ....";
            $expected_message3 = PHP_EOL . "Failed to delete `{$dest_controller_class_file}`. Please delete it manually.";
            $expected_message4 = PHP_EOL . "Goodbye!!";
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::FAILURE_EXIT, $return_val->getReturnCode());
            self::assertStringContainsString($expected_message, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message2, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message3, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message4, $return_val->getReturnMessage());

            // clean-up
            $this->rmdirRecursive($src_path . DIRECTORY_SEPARATOR . 'controllers');
            $this->rmdirRecursive($src_path . DIRECTORY_SEPARATOR . 'views');
        }
        
        $mock->disable();
        $mock2->disable();
    }
    
    public function testThatCreateControllerWorksAsExpectedWithValidParamsAndNoNamespaceAndNoExtendsClassSupplied() {
        
        $src_path = dirname(SMVC_APP_ROOT_PATH . PHP_EOL) . DIRECTORY_SEPARATOR 
                    . 'test-create-controller-output' . DIRECTORY_SEPARATOR . 'src';
        
        $dest_controller_class_file = 
            $src_path . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR 
                      . 'BlogComments.php';
        $dest_view_file = 
            $src_path . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR 
                      . 'blog-comments' . DIRECTORY_SEPARATOR . 'index.php';
        
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'blog-comments',
                '--path-to-src-folder', $src_path,
                //'--extends-controller', \SlimMvcTools\Controllers\BaseController::class,
                //'--namespace-4-controller', "MyApp\\Controllers",
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'blog-comments',
                '-p', $src_path,
                //'-e', \SlimMvcTools\Controllers\BaseController::class,
                //'-n', "MyApp\\Controllers",
            ],
        ];
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            
            $expected_message = "Creating Controller Class `BlogComments` in `{$dest_controller_class_file}` ....";
            $expected_message2 = PHP_EOL . "Successfully created `{$dest_controller_class_file}` ....".PHP_EOL;
            $expected_message3 = PHP_EOL . "Creating index view for `BlogComments::actionIndex()` in `{$dest_view_file}` ....";
            $expected_message4 = PHP_EOL . "Successfully created `{$dest_view_file}` ....".PHP_EOL;
            $expected_message5 = PHP_EOL . "All done!!";
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::SUCCESS_EXIT, $return_val->getReturnCode());
            
            // assert no namespace was decalred in the generated controller class
            self::assertStringNotContainsString('namespace', file_get_contents($dest_controller_class_file));
            
            // assert default base controller was extended in the generated controller class
            self::assertStringContainsString("extends \\SlimMvcTools\\Controllers\\BaseController", file_get_contents($dest_controller_class_file));
            
            self::assertStringContainsString($expected_message, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message2, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message3, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message4, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message5, $return_val->getReturnMessage());
            
            // clean-up
            $this->rmdirRecursive($src_path . DIRECTORY_SEPARATOR . 'controllers');
            $this->rmdirRecursive($src_path . DIRECTORY_SEPARATOR . 'views');
        }
    }
    
    public function testThatCreateControllerWorksAsExpectedWithValidParamsAndNamespaceAndExtendsClassSupplied() {
        
        $src_path = dirname(SMVC_APP_ROOT_PATH . PHP_EOL) . DIRECTORY_SEPARATOR 
                    . 'test-create-controller-output' . DIRECTORY_SEPARATOR . 'src';
        
        $dest_controller_class_file = 
            $src_path . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR 
                      . 'BlogComments.php';
        $dest_view_file = 
            $src_path . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR 
                      . 'blog-comments' . DIRECTORY_SEPARATOR . 'index.php';
        
        $argvs = [
            [
                'smvc-create-controller', //script name is always at index 0
                '--controller-name', 'blog-comments',
                '--path-to-src-folder', $src_path,
                '--extends-controller', \SMVCTools\Tests\TestObjects\ChildController::class,
                '--namespace-4-controller', "MyApp\\Controllers",
            ],
            [
                'smvc-create-controller', //script name is always at index 0
                '-c', 'blog-comments',
                '-p', $src_path,
                '-e', \SMVCTools\Tests\TestObjects\ChildController::class,
                '-n', "MyApp\\Controllers",
            ],
        ];
        
        foreach ($argvs as $argv) {
            
            $argc = count($argv);
            $return_val = \SlimMvcTools\Functions\CliHelpers\createController($argc, $argv);
            
            $expected_message = "Creating Controller Class `BlogComments` in `{$dest_controller_class_file}` ....";
            $expected_message2 = PHP_EOL . "Successfully created `{$dest_controller_class_file}` ....".PHP_EOL;
            $expected_message3 = PHP_EOL . "Creating index view for `BlogComments::actionIndex()` in `{$dest_view_file}` ....";
            $expected_message4 = PHP_EOL . "Successfully created `{$dest_view_file}` ....".PHP_EOL;
            $expected_message5 = PHP_EOL . "All done!!";
            
            self::assertEquals(\SlimMvcTools\Functions\CliHelpers\CliExitCodes::SUCCESS_EXIT, $return_val->getReturnCode());
            
            // assert namespace was decalred in the generated controller class
            self::assertStringContainsString('namespace MyApp\\Controllers', file_get_contents($dest_controller_class_file));
            
            // assert specified base controller was extended in the generated controller class
            self::assertStringContainsString("extends SMVCTools\\Tests\\TestObjects\\ChildController", file_get_contents($dest_controller_class_file));
            
            self::assertTrue(file_exists($dest_view_file));
            
            self::assertStringContainsString($expected_message, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message2, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message3, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message4, $return_val->getReturnMessage());
            self::assertStringContainsString($expected_message5, $return_val->getReturnMessage());
            
            // clean-up
            $this->rmdirRecursive($src_path . DIRECTORY_SEPARATOR . 'controllers');
            $this->rmdirRecursive($src_path . DIRECTORY_SEPARATOR . 'views');
        }
    }
}
