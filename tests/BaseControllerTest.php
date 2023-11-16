<?php
declare(strict_types=1);

use \SlimMvcTools\Controllers\BaseController;

/**
 * Description of BaseControllerTest
 *
 * @author rotimi
 */
class BaseControllerTest extends \PHPUnit\Framework\TestCase
{
    use BaseControllerDependenciesTrait;
    
    protected function setUp(): void {
        
        parent::setUp();
    }
    
    // This must be the first test in this file after the setUp() method so that
    // it gets executed first. It modifies $_SESSION, so it should be tested first.
    public function testThat_storeCurrentUrlForLoginRedirection_WorksAsExpected2() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $unstorable_actions = [
            'login', 'action-login', 'actionlogin', 'action_login',
            'logout', 'action-logout', 'actionlogout', 'action_logout'
        ];
        
        foreach ($unstorable_actions as $action) {
            
            $controller = new BaseController(
                $psr11Container, '', $action, $req, $resp
            );
            
            self::assertSame($controller, $controller->storeCurrentUrlForLoginRedirection());
            self::assertArrayNotHasKey(BaseController::SESSN_PARAM_LOGIN_REDIRECT, $_SESSION);
        }
        
        //////////////////////////////////////////
        $controller2 = new BaseController(
            $psr11Container, 
            '', '', 
            $req->withHeader('X-Requested-With', 'XMLHttpRequest'), 
            $resp
        );
        self::assertSame($controller2, $controller2->storeCurrentUrlForLoginRedirection());
        self::assertArrayNotHasKey(BaseController::SESSN_PARAM_LOGIN_REDIRECT, $_SESSION);

        //////////////////////////////////////////
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $controller3 = new BaseController($psr11Container, 'da-controller', 'da-action', $req, $resp);
        
        self::assertSame($controller3, $controller3->storeCurrentUrlForLoginRedirection());
        self::assertArrayNotHasKey(BaseController::SESSN_PARAM_LOGIN_REDIRECT, $_SESSION);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);

        // Now that we don't have a login route or ajax request
        // the method should store the current url in session
        // http://google.com will not be stored in session,
        // #yoo?foo=1&bar=2 will be appended to the 
        // base-path/controller/action in this case
        // $controller3->getAppBasePath()/da-controller/da-action
        // and what will be stored in session is the string value of
        // $controller3->getAppBasePath()/da-controller/da-action#yoo?foo=1&bar=2
        $controller3->setRequest($this->newRequest('http://google.com/#yoo?foo=1&bar=2'));
        self::assertSame($controller3, $controller3->storeCurrentUrlForLoginRedirection());
        self::assertArrayHasKey(BaseController::SESSN_PARAM_LOGIN_REDIRECT, $_SESSION);
        self::assertStringContainsString($controller3->getAppBasePath(), $_SESSION[BaseController::SESSN_PARAM_LOGIN_REDIRECT]);
        self::assertStringContainsString('/da-controller/da-action#yoo?foo=1&bar=2', $_SESSION[BaseController::SESSN_PARAM_LOGIN_REDIRECT]);
    }

    public function testThat_Constructor_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/da-controller/da-action/');
        $req2 = $this->newRequest('http://google.com/da-controller/da-action');
        
        $req3 = $this->newRequest('http://google.com/da-controller/');
        $req4 = $this->newRequest('http://google.com/da-controller');
        
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, 'a-controller', 'an-action', $req, $resp
        );
        
        self::assertSame($psr11Container, $controller->getContainer());
        self::assertSame($req, $controller->getRequest());
        self::assertSame($resp, $controller->getResponse());
        self::assertEquals(sMVC_UriToString($req->getUri()), $controller->getCurrentUri());
        self::assertEquals('an-action', $controller->getActionNameFromUri());
        self::assertEquals('a-controller', $controller->getControllerNameFromUri());
        
        //////////////////////////////////////////////////////////
        // Start: Test auto-calculation of controller & action
        // from the path of the the uri of the request object
        // when a blank controller & / action are passed to the
        // constructor
        //////////////////////////////////////////////////////////
        
        //////////////////////////////////////////////////////////
        // use request with this path /da-controller/da-action/
        $controller1 = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        self::assertEquals('da-action', $controller1->getActionNameFromUri());
        self::assertEquals('da-controller', $controller1->getControllerNameFromUri());
        
        //////////////////////////////////////////////////////////
        // use request with this path /da-controller/da-action
        $controller2 = new BaseController(
            $psr11Container, '', '', $req2, $resp
        );
        self::assertEquals('da-action', $controller2->getActionNameFromUri());
        self::assertEquals('da-controller', $controller2->getControllerNameFromUri());
        
        //////////////////////////////////////////////////////////
        // use request with this path /da-controller/
        $controller3 = new BaseController(
            $psr11Container, '', '', $req3, $resp
        );
        self::assertEquals('', $controller3->getActionNameFromUri());
        self::assertEquals('da-controller', $controller3->getControllerNameFromUri());
        
        //////////////////////////////////////////////////////////
        // use request with this path /da-controller
        $controller4 = new BaseController(
            $psr11Container, '', 'action-from-constructor', $req4, $resp
        );
        self::assertEquals('action-from-constructor', $controller4->getActionNameFromUri());
        self::assertEquals('da-controller', $controller4->getControllerNameFromUri());
        
        //////////////////////////////////////////////////////////
        // End: Test auto-calculation of controller & action
        // from the path of the the uri of the request object
        // when a blank controller & / action are passed to the
        // constructor
        //////////////////////////////////////////////////////////
        
        
        //////////////////////////////////////////////////////////
        // Test that when no controller and no action are in the
        // request uri, that the controller & action passed to the 
        // constructor are computed into controller's 
        // current_uri_computed property
        //////////////////////////////////////////////////////////
        $req5 = $this->newRequest('http://google.com/');
        $controller5 = new BaseController(
            $psr11Container, 'controller-from-constructor', 
            'action-from-constructor', $req5, $resp
        );
        self::assertEquals('action-from-constructor', $controller5->getActionNameFromUri());
        self::assertEquals('controller-from-constructor', $controller5->getControllerNameFromUri());
        self::assertEquals(
            'http://google.com/controller-from-constructor/action-from-constructor', 
            $controller5->getCurrentUriComputed()
        );
    }

    public function testThat_getActionNameFromUri_WorksAsExpected() {
        
        $req = $this->newRequest();
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, 'base-controller', '', $req, $resp
        );
        self::assertEquals('', $controller->getActionNameFromUri());   
        
        $controller2 = new BaseController(
            $psr11Container, 'base-controller', 'da-action', $req, $resp
        );
        self::assertEquals('da-action', $controller2->getActionNameFromUri());   
    }

    public function testThat_getControllerNameFromUri_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        self::assertEquals('', $controller->getControllerNameFromUri());   
        
        $controller2 = new BaseController(
            $psr11Container, 'base-controller', 'da-action', $req, $resp
        );
        self::assertEquals('base-controller', $controller2->getControllerNameFromUri());   
    }

    public function testThat_getCurrentUri_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        self::assertEquals(sMVC_UriToString($req->getUri()), $controller->getCurrentUri());   
        
        $controller2 = new BaseController(
            $psr11Container, 'base-controller', 'da-action', $req, $resp
        );
        self::assertEquals(sMVC_UriToString($req->getUri()), $controller2->getCurrentUri());   
    }

    public function testThat_getCurrentUriComputed_WorksAsExpected() {
        
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        // container with ['settings']['app_base_path'] with no leading slash
        $psr11Container2 = $this->getContainer(['app_base_path' => 'da-path2',]);
        
        //////////////////////////////////////////////////////////
        // Test that when no controller and no action are in the
        // request uri, that the controller & action passed to the 
        // constructor are computed into controller's 
        // current_uri_computed property
        //////////////////////////////////////////////////////////
        $req = $this->newRequest('http://google.com/');
        $controller = new BaseController(
            $psr11Container, 'controller-from-constructor', 
            'action-from-constructor', $req, $resp
        );
        self::assertEquals(
            'http://google.com/controller-from-constructor/action-from-constructor', 
            $controller->getCurrentUriComputed()
        );
        
        //////////////////////////////////////////////////////////
        // Test that when no controller and no action are in the
        // request uri, that the controller & empty action passed 
        // to the constructor are computed into controller's 
        // current_uri_computed property
        //////////////////////////////////////////////////////////
        $req2 = $this->newRequest('http://google.com/');
        $controller2 = new BaseController(
            $psr11Container, 'controller-from-constructor', '', $req2, $resp
        );
        self::assertEquals(
            'http://google.com/controller-from-constructor', 
            $controller2->getCurrentUriComputed()
        );
        
        
        ////////////////////////////////////////////////////////////////////////
        // Tests with base path with leading slash
        ////////////////////////////////////////////////////////////////////////
        
        //////////////////////////////////////////////////////////
        // Test that when no controller and no action are in the
        // request uri, that the controller & action passed to the 
        // constructor are computed into controller's 
        // current_uri_computed property
        //////////////////////////////////////////////////////////
        $req3 = $this->newRequest('http://google.com' . $psr11Container->get('settings')['app_base_path'] );
        $controller3 = new BaseController(
            $psr11Container, 'controller-from-constructor', 
            'action-from-constructor', $req3, $resp
        );
        self::assertEquals(
            'http://google.com'. $psr11Container->get('settings')['app_base_path'] .'/controller-from-constructor/action-from-constructor', 
            $controller3->getCurrentUriComputed()
        );
        
        //////////////////////////////////////////////////////////
        // Test that when no controller and no action are in the
        // request uri, that the controller & empty action passed 
        // to the constructor are computed into controller's 
        // current_uri_computed property
        //////////////////////////////////////////////////////////
        $req4 = $this->newRequest('http://google.com' . $psr11Container->get('settings')['app_base_path'] );
        $controller4 = new BaseController(
            $psr11Container, 'controller-from-constructor', '', $req4, $resp
        );
        self::assertEquals(
            'http://google.com'. $psr11Container->get('settings')['app_base_path'] .'/controller-from-constructor', 
            $controller4->getCurrentUriComputed()
        );
        
        ////////////////////////////////////////////////////////////////////////
        // Tests with base path with no leading slash
        ////////////////////////////////////////////////////////////////////////
        
        //////////////////////////////////////////////////////////
        // Test that when no controller and no action are in the
        // request uri, that the controller & action passed to the 
        // constructor are computed into controller's 
        // current_uri_computed property
        //////////////////////////////////////////////////////////
        
        $req5 = $this->newRequest('http://google.com/' . $psr11Container2->get('settings')['app_base_path'] );
        $controller5 = new BaseController(
            $psr11Container2, 'controller-from-constructor', 
            'action-from-constructor', $req5, $resp
        );
        self::assertEquals(
            'http://google.com/'. $psr11Container2->get('settings')['app_base_path'] .'/controller-from-constructor/action-from-constructor', 
            $controller5->getCurrentUriComputed()
        );
        
        //////////////////////////////////////////////////////////
        // Test that when no controller and no action are in the
        // request uri, that the controller & empty action passed 
        // to the constructor are computed into controller's 
        // current_uri_computed property
        //////////////////////////////////////////////////////////
        $req6 = $this->newRequest('http://google.com/' . $psr11Container2->get('settings')['app_base_path'] );
        $controller6 = new BaseController(
            $psr11Container2, 'controller-from-constructor', '', $req6, $resp
        );
        self::assertEquals(
            'http://google.com/'. $psr11Container2->get('settings')['app_base_path'] .'/controller-from-constructor', 
            $controller6->getCurrentUriComputed()
        );
    }

    public function testThat_GetSetVespulaAuthObject_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        $new_auth = $this->newVespulaAuth();
        $controller->setVespulaAuthObject($new_auth);
        
        self::assertSame($new_auth, $controller->getVespulaAuthObject());
    }

    public function testThat_GetSetLayoutRenderer_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        $new_renderer = $this->getContainer()['new_layout_renderer'];
        $controller->setLayoutRenderer($new_renderer);
        
        self::assertSame($new_renderer, $controller->getLayoutRenderer());
    }

    public function testThat_GetSetViewRenderer_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        $new_renderer = $this->getContainer()['new_view_renderer'];
        $controller->setViewRenderer($new_renderer);
        
        self::assertSame($new_renderer, $controller->getViewRenderer());
    }

    public function testThat_GetSetRequest_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        $new_req = $this->newRequest();
        $controller->setRequest($new_req);
        
        self::assertSame($new_req, $controller->getRequest());
    }

    public function testThat_GetSetResponse_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        $new_resp = $this->newResponse();
        $controller->setResponse($new_resp);
        
        self::assertSame($new_resp, $controller->getResponse());
    }

    public function testThat_getAppBasePath_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        self::assertEquals(
            $psr11Container->get('settings')['app_base_path'], 
            $controller->getAppBasePath()
        );
    }

    public function testThat_makeLink_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        self::assertEquals(
            rtrim($controller->getAppBasePath(), '/') . '/', 
            $controller->makeLink('/')
        );
        
        self::assertEquals(
            rtrim($controller->getAppBasePath(), '/') . '/da-controller', 
            $controller->makeLink('/da-controller')
        );
        
        self::assertEquals(
            rtrim($controller->getAppBasePath(), '/') . '/da-controller/', 
            $controller->makeLink('/da-controller/')
        );
        
        self::assertEquals(
            rtrim($controller->getAppBasePath(), '/') . '/da-controller/da-action/', 
            $controller->makeLink('/da-controller/da-action/')
        );
        
        self::assertEquals(
            rtrim($controller->getAppBasePath(), '/') . '/da-controller/da-action', 
            $controller->makeLink('/da-controller/da-action')
        );
    }
    
    public function testThat_renderLayout_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        $path_2_layout_files = __DIR__ . DIRECTORY_SEPARATOR . 'test-template-output' . DIRECTORY_SEPARATOR;
        self::assertEquals(
            file_get_contents("{$path_2_layout_files}sample-layout-rendered.txt"), 
            $controller->renderLayout('sample-layout.php', ['boo' => 'Boo Boo'])
        );
    }
    
    public function testThat_renderView_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        //////////////////////////////////////////////////////////////////////
        // Test that the index file in the default 
        // ./tests/fake-smvc-app-root/src/views/base gets rendered
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        self::assertEquals(
            'Base Controller Index File Hello World', 
            $controller->renderView('index.php', ['hi' => 'Hello World'])
        );
        
        //////////////////////////////////////////////////////////////////////
        // Test that the index file for child-controller is rendered
        $controller2 = new \SMVCTools\Tests\TestObjects\ChildController(
            $psr11Container, '', '', $req, $resp
        );
        self::assertEquals(
            'Child Controller Index File', 
            $controller2->renderView('index.php', [])
        );
        self::assertEquals('child-controller', $controller2->getControllerNameFromUri());
        self::assertEquals('', $controller2->getActionNameFromUri());
        
        //////////////////////////////////////////////////////////////////////
        // Test that the index file for grand-child-controller is rendered
        $controller3 = new \SMVCTools\Tests\TestObjects\GrandChildController(
            $psr11Container, '', '', $req, $resp
        );
        self::assertEquals(
            'Grand Child Controller Index File', 
            $controller3->renderView('index.php', [])
        );
        self::assertEquals('grand-child-controller', $controller3->getControllerNameFromUri());
        self::assertEquals('', $controller3->getActionNameFromUri());
    }
    
    public function testThat_actionIndex_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        self::assertEquals(
            'Layout: View: Base Controller Index File ', 
            $controller->actionIndex()
        );
    }

    public function testThat_actionRoutes_WorksAsExpected() {
        
        if(!defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES')){

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new \SMVCTools\Tests\TestObjects\ChildController(
            $psr11Container, '', '', $req, $resp
        );
        
        // calling actionRoutes when not logged in should return a response 
        // object redirecting to login page
        $result = $controller->actionRoutes(1);
        
        self::assertInstanceOf(
            \Psr\Http\Message\ResponseInterface::class, 
            $result
        );
        
        self::assertTrue($result->hasHeader('Location'));
        self::assertEquals(302, $result->getStatusCode());
        self::assertStringContainsString('login', $result->getHeaderLine('Location'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testThat_actionRoutes_WorksAsExpected2() {
        
        if(!defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES')){

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new \SMVCTools\Tests\TestObjects\ChildController(
            $psr11Container, '', '', $req, $resp
        );
        
        // force login
        $controller->is_logged_in = true;
        $result = $controller->actionRoutes(1);
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'test-template-output' . DIRECTORY_SEPARATOR;
        self::assertEquals(
            file_get_contents("{$path}action-routes-output.txt"), 
            $result
        );
    }
    
    public function testThat_getContainerItem_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );        
        self::assertSame($psr11Container->get('settings'), $controller->getContainerItem('settings'));
        self::assertSame($psr11Container->get('namespaces_for_controllers'), $controller->getContainerItem('namespaces_for_controllers'));
        

        try {
            $controller->getContainerItem('non-existent-item');
            $this->fail(\Slim\Exception\HttpInternalServerErrorException::class . ' was not thrown');
            
        } catch (\Slim\Exception\HttpInternalServerErrorException $exc) {

            $expected_msg = "ERROR: The item with the key named `non-existent-item`"
                      . " does not exist in the container associated with"
                      . " `SlimMvcTools\\Controllers\\BaseController` .";
            
            self::assertStringContainsString(
                $expected_msg, $exc->getMessage()
            );
        } 
    }
    
    public function testThat_getContainer_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );        
        self::assertSame($psr11Container, $controller->getContainer());
    }
    
    public function testThat_forceHttpMethods_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        $force_methods = [
            'forceHttp400'=> \Slim\Exception\HttpBadRequestException::class,
            'forceHttp401'=> \Slim\Exception\HttpUnauthorizedException::class,
            'forceHttp403'=> \Slim\Exception\HttpForbiddenException::class,
            'forceHttp404'=> \Slim\Exception\HttpNotFoundException::class,
            'forceHttp405'=> \Slim\Exception\HttpMethodNotAllowedException::class,
            'forceHttp410'=> \Slim\Exception\HttpGoneException::class,
            'forceHttp500'=> \Slim\Exception\HttpInternalServerErrorException::class,
            'forceHttp501'=> \Slim\Exception\HttpNotImplementedException::class,
        ];
        
        foreach($force_methods as $force_method => $exception_class) {
            
            try {
                $controller->$force_method($force_method);
                $this->fail($exception_class . ' was not thrown');

            } catch (\Slim\Exception\HttpException $exc) {

                self::assertStringContainsString(
                  "{$force_method}", $exc->getMessage()
                );
                self::assertInstanceOf($exception_class, $exc);
            } 

            try {
                $controller->$force_method("{$force_method}-2", $this->newRequest());
                $this->fail($exception_class . ' was not thrown');

            } catch (\Slim\Exception\HttpException $exc) {

                self::assertStringContainsString(
                  "{$force_method}-2", $exc->getMessage()
                );
                self::assertInstanceOf($exception_class, $exc);
            }
        } // foreach($force_methods as $force_method => $exception_class)        
    }
}
