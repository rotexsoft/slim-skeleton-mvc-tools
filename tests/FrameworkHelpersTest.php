<?php
declare(strict_types=1);

use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;

/**
 * Description of StrHelpersTest
 *
 * @author rotimi
 */
class FrameworkHelpersTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void {
        
        parent::setUp();
    }
    
    protected function newResponse(): \Psr\Http\Message\ResponseInterface {
        
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $responseBody = $psr17Factory->createStream('Hello world');
        $response = $psr17Factory->createResponse(200)->withBody($responseBody);
        
        return $response;
    }
    
    protected function newRequest(): \Psr\Http\Message\ServerRequestInterface {
        
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $request = $psr17Factory->createServerRequest('GET', 'http://tnyholm.se/blah?var=1');
        
        return $request;
    }
    
    protected function newVespulaAuth(): \Vespula\Auth\Auth {

        $pdo = new \PDO(
                    'sqlite::memory:', 
                    null, 
                    null, 
                    [
                        PDO::ATTR_PERSISTENT => true, 
                        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
                    ]
                );
        $pass1 = password_hash('admin' , PASSWORD_DEFAULT);
        $pass2 = password_hash('root' , PASSWORD_DEFAULT);

        $sql = <<<SQL
DROP TABLE IF EXISTS "user_authentication_accounts";
CREATE TABLE user_authentication_accounts (
    username VARCHAR(255), password VARCHAR(255)
);
INSERT INTO "user_authentication_accounts" VALUES( 'admin', '$pass1' );
INSERT INTO "user_authentication_accounts" VALUES( 'root', '$pass2' );
SQL;
        $pdo->exec($sql); //add two default user accounts

        //Optionally pass a maximum idle time and a time until the session 
        //expires (in seconds)
        $expire = 3600;
        $max_idle = 1200;
        $session = new \Vespula\Auth\Session\Session($max_idle, $expire);

        $cols = ['username', 'password'];
        $from = 'user_authentication_accounts';
        $where = ''; //optional
        $adapter = new \Vespula\Auth\Adapter\Sql($pdo, $from, $cols, $where);

        return new \Vespula\Auth\Auth($adapter, $session);
    }

    public function testThat_sMVC_CreateController_WorksAsExpected() {
        
        /////////////////////////////////////////////////
        // START: Setup Container, Request & Response  
        // objects needed by sMVC_CreateController
        /////////////////////////////////////////////////
        $container = new Container();
        $container['settings'] = [
            'displayErrorDetails' => false,
            'logErrors' => false,
            'logErrorDetails' => false,
            'addContentLengthHeader' => true,
            
            'app_base_path' => '',
            'error_template_file'=> 'error-template.php',
            'use_mvc_routes' => true,
            'mvc_routes_http_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            'auto_prepend_action_to_action_method_names' => false,
            'default_controller_class_name' => \SlimMvcTools\Controllers\BaseController::class,
            'default_action_name' => 'actionIndex',

            'error_handler_class' => \SlimMvcTools\ErrorHandler::class,
            'html_renderer_class' => \SlimMvcTools\HtmlErrorRenderer::class,
            'log_renderer_class'  => \SlimMvcTools\LogErrorRenderer::class,
        ];
        $container['namespaces_for_controllers'] = [
            '\\SlimMvcTools\\Controllers\\',
            '\\SMVCTools\\Tests\\TestObjects\\',
        ];
        $psr11Container = new PsrContainer($container);
        
        $req = $this->newRequest();
        $resp = $this->newResponse();
        /////////////////////////////////////////////
        // END: Setup Container, Request & Response  
        // objects needed by sMVC_CreateController
        /////////////////////////////////////////////
        
        ////////////////////////////////////////////////////////////////
        // controller within first namespace \SlimMvcTools\Controllers\
        ////////////////////////////////////////////////////////////////
        $controller1 = sMVC_CreateController(
            $psr11Container, 'base-controller', '', $req, $resp
        );
        self::assertInstanceOf(\SlimMvcTools\Controllers\BaseController::class, $controller1);
        
        //////////////////////////////////////////////////////////////////////
        // controller within second namespace \SMVCTools\Tests\TestObjects\
        //////////////////////////////////////////////////////////////////////
        $controller2 = sMVC_CreateController(
            $psr11Container, 'test-controller-with-name-space', '', $req, $resp
        );
        self::assertInstanceOf(\SMVCTools\Tests\TestObjects\TestControllerWithNameSpace::class, $controller2);
        
        /////////////////////////////////
        // controller without namespace
        /////////////////////////////////
        $controller3 = sMVC_CreateController(
            $psr11Container, 'test-controller-no-name-space', '', $req, $resp
        );
        self::assertInstanceOf(\TestControllerNoNameSpace::class, $controller3);
        
        /////////////////////////////////////////
        // when a bad controller name is supplied
        /////////////////////////////////////////
        try {
            sMVC_CreateController($psr11Container, '11base-controller', '', $req, $resp);
            $this->fail(\Slim\Exception\HttpBadRequestException::class . ' was not thrown');
            
        } catch (\Slim\Exception\HttpBadRequestException $exc) {
            
            self::assertStringContainsString(
              "Bad controller name `11baseController`", $exc->getMessage()
            );
        }
        
        ////////////////////////////////////////////////////////////////////////////////////
        // when a valid controller name that does not have a corresponding class is supplied
        ////////////////////////////////////////////////////////////////////////////////////
        try {
            sMVC_CreateController($psr11Container, 'base-controller-not-existent', '', $req, $resp);
            $this->fail(\Slim\Exception\HttpNotFoundException::class . ' was not thrown');
            
        } catch (\Slim\Exception\HttpNotFoundException $exc) {
            
            self::assertStringContainsString(
              "Class `BaseControllerNotExistent` does not exist.", $exc->getMessage()
            );
        }
        
        ///////////////////////////////////////////////////////////////////
        // when a valid controller name that does not map to a subclass of
        // \SlimMvcTools\Controllers\BaseController is supplied
        ///////////////////////////////////////////////////////////////////
        try {
            sMVC_CreateController($psr11Container, 'non-controller', '', $req, $resp);
            $this->fail(\Slim\Exception\HttpBadRequestException::class . ' was not thrown');
            
        } catch (\Slim\Exception\HttpBadRequestException $exc) {
            
            self::assertStringContainsString(
              "` could not be mapped to a valid controller.", $exc->getMessage()
            );
        }    
    }
    
    public function testThat_sMVC_DumpAuthinfo_WorksAsExpected() {
        
        $result = sMVC_DumpAuthinfo($this->newVespulaAuth());
        self::assertStringContainsString('Login Status: ', $result);
        self::assertStringContainsString('Logged in Person\'s Username: ', $result);
        self::assertStringContainsString('Logged in User\'s Data: ', $result);
    }
    
    public function testThat_sMVC_DumpVar_WorksAsExpected() {
        
        $expected_needle = 'string(11) "Hello world"';
        $expected_needle2 = 'int(777)';
        $func_wrapper = function(...$vals) { sMVC_DumpVar(...$vals); };
        
        $result = $this->execVoidFuncCaptureAndReturnOutput($func_wrapper, "Hello world");
        self::assertStringContainsString($expected_needle, $result);
        
        $result2 = $this->execVoidFuncCaptureAndReturnOutput($func_wrapper, "Hello world", 777);
        self::assertStringContainsString($expected_needle, $result2);
        self::assertStringContainsString($expected_needle2, $result2);
    }
    
    protected function execVoidFuncCaptureAndReturnOutput(callable $func, ...$args) {
        
        // Start capturing the output
        ob_start();

        /** @psalm-suppress ForbiddenCode */
        $func(...$args);

        // Get the captured output, close the buffer & return the captured output
        return ob_get_clean();
    }
    
    public function testThat_sMVC_GetSuperGlobal_WorksAsExpected() {
        
        $original_script_name = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $all_globals = sMVC_GetSuperGlobal();
        
        self::assertArrayHasKey('server', $all_globals);
        self::assertArrayHasKey('get', $all_globals);
        self::assertArrayHasKey('post', $all_globals);
        self::assertArrayHasKey('files', $all_globals);
        self::assertArrayHasKey('cookie', $all_globals);
        self::assertArrayHasKey('env', $all_globals);
        self::assertArrayHasKey('session', $all_globals);
        
        self::assertEquals(isset($_SERVER)? $_SERVER : [], $all_globals['server']);
        self::assertEquals(isset($_GET)? $_GET : [], $all_globals['get']);
        self::assertEquals(isset($_POST)? $_POST : [], $all_globals['post']);
        self::assertEquals(isset($_FILES)? $_FILES : [], $all_globals['files']);
        self::assertEquals(isset($_COOKIE)? $_COOKIE : [], $all_globals['cookie']);
        self::assertEquals(isset($_ENV)? $_ENV : [], $all_globals['env']);
        self::assertEquals($_SESSION, $all_globals['session']);
        
        self::assertEquals(isset($_SERVER)? $_SERVER : [], sMVC_GetSuperGlobal('$_server'));
        self::assertEquals(isset($_GET)? $_GET : [], sMVC_GetSuperGlobal('$_get'));
        self::assertEquals(isset($_POST)? $_POST : [], sMVC_GetSuperGlobal('$_post'));
        self::assertEquals(isset($_FILES)? $_FILES : [], sMVC_GetSuperGlobal('$_files'));
        self::assertEquals(isset($_COOKIE)? $_COOKIE : [], sMVC_GetSuperGlobal('$_cookie'));
        self::assertEquals(isset($_ENV)? $_ENV : [], sMVC_GetSuperGlobal('$_env'));
        self::assertEquals($_SESSION, sMVC_GetSuperGlobal('$_session'));
        
        self::assertEquals([], sMVC_GetSuperGlobal('non-existent'));
        self::assertEquals(__FILE__, sMVC_GetSuperGlobal('server', 'SCRIPT_NAME'));
        self::assertEquals('boo', sMVC_GetSuperGlobal('server', 'non-existent', 'boo'));
        
        $_SERVER['SCRIPT_NAME'] = $original_script_name;
    }

    /**
     * @runInSeparateProcess
     */
    public function testThat_sMVC_GetSuperGlobal_WorksAsExpected2() {
        
        session_abort();
        $all_globals = sMVC_GetSuperGlobal();
        self::assertArrayHasKey('session', $all_globals);
        self::assertNull($all_globals['session']);
        self::assertEquals('boo', sMVC_GetSuperGlobal('session', 'non-existent', 'boo'));
    }
    
    public function testThat_sMVC_UriToString_WorksAsExpected() {
        
        $result = sMVC_UriToString($this->newRequest()->getUri());
        self::assertEquals('http://tnyholm.se/blah?var=1', $result);
    }
    
    public function testThat_sMVC_addQueryStrParamToUri_WorksAsExpected() {
        
        $result = sMVC_addQueryStrParamToUri(
                    $this->newRequest()->getUri(),
                    'baa', 'yoo'
                );
        self::assertEquals('http://tnyholm.se/blah?var=1&baa=yoo', sMVC_UriToString($result));
    }
    
    public function testThat_sMVC_DisplayAndLogFrameworkFileNotFoundError_WorksAsExpected() {
        
        $error_message = 'Test Error';
        $file_path = '/missing/file/path'; 
        $dist_file_path = '/dist/file/path'; 
        $app_root_path = __DIR__ . DIRECTORY_SEPARATOR . 'test-template-output';
        $func_wrapper = function(...$vals) { sMVC_DisplayAndLogFrameworkFileNotFoundError(...$vals); };
        
        $result = $this->execVoidFuncCaptureAndReturnOutput(
            $func_wrapper, $error_message, $file_path, $dist_file_path, $app_root_path
        );
        self::assertStringContainsString($error_message, $result);
        
        $ds = DIRECTORY_SEPARATOR;
        $log_file = $app_root_path . "{$ds}logs{$ds}daily_log_" . date('Y_M_d') . '.txt';
        self::assertFileExists($log_file);
        
        $log_file_contents = file_get_contents($log_file);
        self::assertStringContainsString($file_path, $log_file_contents);
        self::assertStringContainsString($dist_file_path, $log_file_contents);
    }
    
    public function testThat_sMVC_DoGetCurrentAppEnvironment_WorksAsExpected() {
        
        $app_root_path = __DIR__ . DIRECTORY_SEPARATOR . 'test-template-output';
        $result = sMVC_DoGetCurrentAppEnvironment($app_root_path);
        self::assertEquals('testing', $result);
    }
    
    public function testThat_sMVC_PrependAction2ActionMethodName_WorksAsExpected() {
        
        $result = sMVC_PrependAction2ActionMethodName('da-action');
        $result2 = sMVC_PrependAction2ActionMethodName('action-da-action'); // no effect, already prefixed with action
        self::assertEquals('actionDa-action', $result);
        self::assertEquals('action-da-action', $result2);
    }
}
