<?php
declare(strict_types=1);

use \SlimMvcTools\Controllers\BaseController,
    \SlimMvcTools\ContainerKeys;

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
    
    protected function tearDown(): void {
        
        parent::tearDown();
    }
    
    // This must be the first test in this file after the setUp() method so that
    // it gets executed first. It modifies $_SESSION, so it should be tested first.
    /**
     * @runInSeparateProcess
     */
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
        $controller3->setRequest($this->newRequest('http://google.com/da-controller/da-action#yoo?foo=1&bar=2'));
        self::assertSame($controller3, $controller3->storeCurrentUrlForLoginRedirection());
        self::assertArrayHasKey(BaseController::SESSN_PARAM_LOGIN_REDIRECT, $_SESSION);
        
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
        
        //////////////////////////////////////////////////////////
        
        // Make sure that uri's with no controller & no action but just
        // forward slashes lead to an empty string value for both
        // getActionNameFromUri() & getControllerNameFromUri()
        
        $req6 = $this->newRequest('http://google.com/');
        $controller6 = new BaseController(
            $psr11Container, '', '', $req6, $resp
        );
        self::assertEquals('', $controller6->getActionNameFromUri());
        self::assertEquals('', $controller6->getControllerNameFromUri());
        
        $req7 = $this->newRequest('http://google.com//');
        $controller7 = new BaseController(
            $psr11Container, '', '', $req7, $resp
        );
        self::assertEquals('', $controller7->getActionNameFromUri());
        self::assertEquals('', $controller7->getControllerNameFromUri());
        
        $req8 = $this->newRequest('http://google.com///');
        $controller8 = new BaseController(
            $psr11Container, '', '', $req8, $resp
        );
        self::assertEquals('', $controller8->getActionNameFromUri());
        self::assertEquals('', $controller8->getControllerNameFromUri());
        
        $req9 = $this->newRequest('http://google.com////');
        $controller9 = new BaseController(
            $psr11Container, '', '', $req9, $resp
        );
        self::assertEquals('', $controller9->getActionNameFromUri());
        self::assertEquals('', $controller9->getControllerNameFromUri());
    }

    public function testThat_getLoginSuccessRedirectAction_WorksAsExpected() {
        
        $req = $this->newRequest();
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, 'base-controller', '', $req, $resp
        );
        self::assertEquals('login-status', $controller->getLoginSuccessRedirectAction());   
        
        $controller2 = new \SMVCTools\Tests\TestObjects\ChildController(
            $psr11Container, 'child-controller', 'da-action', $req, $resp
        );
        self::assertEquals('login-status2', $controller2->getLoginSuccessRedirectAction());
    }

    public function testThat_getLoginSuccessRedirectController_WorksAsExpected() {
        
        $req = $this->newRequest();
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, 'base-controller', '', $req, $resp
        );
        self::assertEquals('base-controller', $controller->getLoginSuccessRedirectController());   
        
        $controller2 = new \SMVCTools\Tests\TestObjects\ChildController(
            $psr11Container, 'child-controller', 'da-action', $req, $resp
        );
        self::assertEquals('child-controller', $controller2->getLoginSuccessRedirectController());
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
        
        $req2 = $this->newRequest('http://google.com/controller/da-action/');
        $controller3 = new BaseController(
            $psr11Container, '', '', $req2, $resp
        );
        self::assertEquals('da-action', $controller3->getActionNameFromUri());
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
        
        $req2 = $this->newRequest('http://google.com/da-controller/da-action/');
        $controller3 = new BaseController(
            $psr11Container, '', '', $req2, $resp
        );
        self::assertEquals('da-controller', $controller3->getControllerNameFromUri());
    }
    
    public function testThat_GetSetVespulaAuthObject_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        self::assertSame($psr11Container->get(ContainerKeys::VESPULA_AUTH), $controller->getVespulaAuthObject());
        
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
        
        $new_renderer = $this->getContainer()[\SlimMvcTools\ContainerKeys::LAYOUT_RENDERER];
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
        
        $new_renderer = $this->getContainer()[\SlimMvcTools\ContainerKeys::VIEW_RENDERER];
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
        self::assertSame($req, $controller->getRequest());
        
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
        self::assertSame($resp, $controller->getResponse());
        
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
            $psr11Container->get(ContainerKeys::APP_SETTINGS)['app_base_path'], 
            $controller->getAppBasePath()
        );
    }

    public function testThat_getAppSetting_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        self::assertEquals(
            $psr11Container->get(ContainerKeys::APP_SETTINGS)['app_base_path'], 
            $controller->getAppSetting('app_base_path')
        );
        self::assertNull( $controller->getAppSetting('non_existent_setting_key') );
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
    
    /**
     * @runInSeparateProcess
     */
    public function testThat_updateSelectedLanguage_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        // no language parameter was specified via a request uri query param
        $controller->updateSelectedLanguage();
        
        self::assertEquals(
            $psr11Container->get(ContainerKeys::DEFAULT_LOCALE), 
            $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->getCode()
        );
        
        // Query param in the request uri
        $param_key = BaseController::GET_QUERY_PARAM_SELECTED_LANG;
        $req2 = $this->newRequest("http://google.com/?{$param_key}=fr_CA");
        $controller2 = new BaseController(
            $psr11Container, '', '', $req2, $resp
        );
        // Language parameter was specified via a request uri query param
        $controller2->updateSelectedLanguage();
        
        self::assertEquals(
            'fr_CA', 
            $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->getCode()
        );
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

    /**
     * @runInSeparateProcess
     */
    public function testThat_actionLogin_WithRequestGetMethod_WorksAsExpected() {
        
        if(!defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES')){

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        ////////////////////////////////////////////////////////////////////////
        // Scenario: Calling actionLogin() when logged out and when request 
        //           method is GET
        ////////////////////////////////////////////////////////////////////////
        $actionResult = $controller->actionLogin();
        
        $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;

        $action_login = ($prepend_action) ? 'action-login' : 'login';
        $login_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/$action_login");

        $action_logout = ($prepend_action) ? 'action-logout' : 'logout';
        $logout_action_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/$action_logout/0");
        
        self::assertStringNotContainsString('<p style="background-color: orange;">', $actionResult); // paragraph containing error message is not present
        
        self::assertStringContainsString("<form action=\"{$login_path}\" method=\"post\">", $actionResult);
        self::assertStringContainsString('<span>User Name: </span>', $actionResult);
        self::assertStringContainsString('<input type="text" name="username" placeholder="User&#x20;Name" value="">', $actionResult);
        self::assertStringContainsString('<span>Password: </span>', $actionResult);
        self::assertStringContainsString('<input type="password" name="password" autocomplete="off" placeholder="Password" value="">', $actionResult);
        self::assertStringContainsString('<input type="submit" value="Login">', $actionResult);
        self::assertStringContainsString('</form>', $actionResult);
        
        self::assertStringNotContainsString("<form action=\"{$logout_action_path}\" method=\"post\">", $actionResult); // logout form should not be present
        self::assertStringNotContainsString('<input type="submit" value="Logout">', $actionResult); // logout button should not be present
        
        ////////////////////////////////////////////////////////////////////////
        // Scenario: Calling actionLogin() when logged in and when request 
        //           method is GET
        ////////////////////////////////////////////////////////////////////////
        $credentials = [ 'username'=> 'admin', 'password'=> 'admin', ];
        
        $controller->getVespulaAuthObject()->login($credentials);
        
        $actionResult2 = $controller->actionLogin();
        
        self::assertTrue($controller->isLoggedIn());
        
        self::assertStringNotContainsString('<p style="background-color: orange;">', $actionResult2); // paragraph containing error message is not present
        
        self::assertStringNotContainsString("<form action=\"{$login_path}\" method=\"post\">", $actionResult2);
        self::assertStringNotContainsString('<span>User Name: </span>', $actionResult2);
        self::assertStringNotContainsString('<input type="text" name="username" placeholder="User Name" value="">', $actionResult2);
        self::assertStringNotContainsString('<span>Password: </span>', $actionResult2);
        self::assertStringNotContainsString('<input type="password" name="password" autocomplete="off" placeholder="Password" value="">', $actionResult2);
        self::assertStringNotContainsString('<input type="submit" value="Login">', $actionResult2);
        
        self::assertStringContainsString("<form action=\"{$logout_action_path}\" method=\"post\">", $actionResult2); // logout form should not be present
        self::assertStringContainsString('<input type="submit" value="Logout">', $actionResult2); // logout button should not be present
        
        $controller->getVespulaAuthObject()->logout(); // logout
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testThat_actionLogin_WithRequestPostMethod_WorksAsExpected() {
        
        if(!defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES')){

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        $req = $this->newRequest('http://google.com/')->withMethod('POST');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;

        $action_login = ($prepend_action) ? 'action-login' : 'login';
        $login_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/$action_login");

        $action_logout = ($prepend_action) ? 'action-logout' : 'logout';
        $logout_action_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/$action_logout/0");
        
        ////////////////////////////////////////////////////////////////////////
        // Scenario: Empty Username & empty Password Should generate Error Message 
        // that is injected to the login form, user will not be redirected.
        // Login form is returned instead of a Response object containing 
        // a redirect uri
        ////////////////////////////////////////////////////////////////////////
        
        //$_POST['username'] = '';
        //$_POST['password'] = '';
        $actionLoginResult = $controller->actionLogin();

        // Response Object with a redirect uri should not be returned in this scenario
        self::assertNotInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionLoginResult);
        
        // Login form with error messages should be returned in this scenario
        self::assertIsString($actionLoginResult);
        self::assertStringContainsString(
            "Layout: View:", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "The 'username' field is empty.<br>The 'password' field is empty.<br>Login Status: ANON<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "Logged in Person's Username: <br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "Logged in User's Data: <br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "Array<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "(<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            ")<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "</p>", 
            $actionLoginResult
        );
        
        self::assertStringContainsString("<form action=\"{$login_path}\" method=\"post\">", $actionLoginResult);
        self::assertStringContainsString('<span>User Name: </span>', $actionLoginResult);
        self::assertStringContainsString('<input type="text" name="username" placeholder="User&#x20;Name" value="">', $actionLoginResult);
        self::assertStringContainsString('<span>Password: </span>', $actionLoginResult);
        self::assertStringContainsString('<input type="password" name="password" autocomplete="off" placeholder="Password" value="">', $actionLoginResult);
        self::assertStringContainsString('<input type="submit" value="Login">', $actionLoginResult);
        self::assertStringContainsString('</form>', $actionLoginResult);
        
        self::assertStringNotContainsString("<form action=\"{$logout_action_path}\" method=\"post\">", $actionLoginResult); // logout form should not be present
        self::assertStringNotContainsString('<input type="submit" value="Logout">', $actionLoginResult); // logout button should not be present
    }

    /**
     * @runInSeparateProcess
     */
    public function testThat_actionLogin_WithRequestPostMethod_WorksAsExpected2() {
        
        if(!defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES')){

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        $req = $this->newRequest('http://google.com/')->withMethod('POST');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;

        $action_login = ($prepend_action) ? 'action-login' : 'login';
        $login_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/$action_login");

        $action_logout = ($prepend_action) ? 'action-logout' : 'logout';
        $logout_action_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/$action_logout/0");        
        
        ////////////////////////////////////////////////////////////////////////
        // Scenario: Non-empty Username & empty Password Should generate Error Message 
        // that is injected to the login form, user will not be redirected.
        // Login form is returned instead of a Response object containing 
        // a redirect uri
        ////////////////////////////////////////////////////////////////////////
        $_POST['username'] = 'admin';
        //$_POST['password'] = '';
        $actionLoginResult = $controller->actionLogin();

        // Response Object with a redirect uri should not be returned in this scenario
        self::assertNotInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionLoginResult);
        
        // Login form with error messages should be returned in this scenario
        self::assertIsString($actionLoginResult);
        self::assertStringContainsString(
            "Layout: View:", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "The 'password' field is empty.<br>Login Status: ANON<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "Logged in Person's Username: <br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "Logged in User's Data: <br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "Array<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "(<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            ")<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "</p>", 
            $actionLoginResult
        );
        
        self::assertStringContainsString("<form action=\"{$login_path}\" method=\"post\">", $actionLoginResult);
        self::assertStringContainsString('<span>User Name: </span>', $actionLoginResult);
        self::assertStringContainsString('<input type="text" name="username" placeholder="User&#x20;Name" value="admin">', $actionLoginResult);
        self::assertStringContainsString('<span>Password: </span>', $actionLoginResult);
        self::assertStringContainsString('<input type="password" name="password" autocomplete="off" placeholder="Password" value="">', $actionLoginResult);
        self::assertStringContainsString('<input type="submit" value="Login">', $actionLoginResult);
        self::assertStringContainsString('</form>', $actionLoginResult);
        
        self::assertStringNotContainsString("<form action=\"{$logout_action_path}\" method=\"post\">", $actionLoginResult); // logout form should not be present
        self::assertStringNotContainsString('<input type="submit" value="Logout">', $actionLoginResult); // logout button should not be present
    }

    /**
     * @runInSeparateProcess
     */
    public function testThat_actionLogin_WithRequestPostMethod_WorksAsExpected3() {
        
        if(!defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES')){

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        $req = $this->newRequest('http://google.com/')->withMethod('POST');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;

        $action_login = ($prepend_action) ? 'action-login' : 'login';
        $login_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/$action_login");

        $action_logout = ($prepend_action) ? 'action-logout' : 'logout';
        $logout_action_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/$action_logout/0");        
        
        ////////////////////////////////////////////////////////////////////////
        // Scenario: Empty Username & non-empty Password Should generate Error Message 
        // that is injected to the login form, user will not be redirected.
        // Login form is returned instead of a Response object containing 
        // a redirect uri
        ////////////////////////////////////////////////////////////////////////
        //$_POST['username'] = '';
        $_POST['password'] = 'admin';
        $actionLoginResult = $controller->actionLogin();

        // Response Object with a redirect uri should not be returned in this scenario
        self::assertNotInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionLoginResult);
        
        // Login form with error messages should be returned in this scenario
        self::assertIsString($actionLoginResult);
        self::assertStringContainsString(
            "Layout: View:", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "The 'username' field is empty.<br>Login Status: ANON<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "Logged in Person's Username: <br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "Logged in User's Data: <br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "Array<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "(<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            ")<br />", 
            $actionLoginResult
        );
        self::assertStringContainsString(
            "</p>", 
            $actionLoginResult
        );
        
        self::assertStringContainsString("<form action=\"{$login_path}\" method=\"post\">", $actionLoginResult);
        self::assertStringContainsString('<span>User Name: </span>', $actionLoginResult);
        self::assertStringContainsString('<input type="text" name="username" placeholder="User&#x20;Name" value="">', $actionLoginResult);
        self::assertStringContainsString('<span>Password: </span>', $actionLoginResult);
        self::assertStringContainsString('<input type="password" name="password" autocomplete="off" placeholder="Password" value="admin">', $actionLoginResult);
        self::assertStringContainsString('<input type="submit" value="Login">', $actionLoginResult);
        self::assertStringContainsString('</form>', $actionLoginResult);
        
        self::assertStringNotContainsString("<form action=\"{$logout_action_path}\" method=\"post\">", $actionLoginResult); // logout form should not be present
        self::assertStringNotContainsString('<input type="submit" value="Logout">', $actionLoginResult); // logout button should not be present
    }

    /**
     * @runInSeparateProcess
     */
    public function testThat_actionLogin_WithRequestPostMethod_WorksAsExpected4() {
        
        if(!defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES')){

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        $req = $this->newRequest('http://google.com/')->withMethod('POST');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        $controller = new BaseController(
            $psr11Container, 'da-contorller', 'da-action', $req, $resp
        );    
        
        ////////////////////////////////////////////////////////////////////////
        // Scenario: Valid Username & Password lead to a successful login and
        // user will be redirected.
        // 
        // Response object containing a redirect uri (which is the uri stored in
        // session when the controller was created) is returned instead of a  
        // Login form
        ////////////////////////////////////////////////////////////////////////
        $_POST['username'] = 'admin';
        $_POST['password'] = 'admin';
        $actionLoginResult = $controller->actionLogin();
        $expectedRedirectPath = "/";

        // Response Object with a redirect uri should not be returned in this scenario
        self::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionLoginResult);
        self::assertEquals(302, $actionLoginResult->getStatusCode());
        self::assertEquals($expectedRedirectPath, $actionLoginResult->getHeaderLine('Location'));
        
        ////////////////////////////////////////////////////////////////////////
        // Response object containing a redirect uri (we remove the uri stored 
        // in session when the controller was created, so that redirect uri will
        // contain the controller and action values stored in the 
        // `login_success_redirect_controller` & `login_success_redirect_action`
        // properties of the controller object) is returned instead of a  
        // Login form
        ////////////////////////////////////////////////////////////////////////
        unset($_SESSION[BaseController::SESSN_PARAM_LOGIN_REDIRECT]);
        $actionLoginResult2 = $controller->actionLogin();


        $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;
        $action = (
                    $prepend_action 
                    && !str_starts_with(mb_strtolower($controller->getLoginSuccessRedirectAction(), 'UTF-8'), 'action')
                  ) 
                  ? 'action-' : '';
        $expectedRedirectPath2 = $controller->makeLink(
            "/{$controller->getLoginSuccessRedirectController()}/{$action}{$controller->getLoginSuccessRedirectAction()}"
        );
            
        $controller->actionLogout(); // logout, clean up
        
        // Response Object with a redirect uri should not be returned in this scenario
        self::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionLoginResult2);
        self::assertEquals(302, $actionLoginResult2->getStatusCode());
        self::assertEquals($expectedRedirectPath2, $actionLoginResult2->getHeaderLine('Location'));
        
        ////////////////////////////////////////////////////////////////////////
        // Repeat this scenario with a non-BaseController
        ////////////////////////////////////////////////////////////////////////
        $controller2 = new \SMVCTools\Tests\TestObjects\ChildController(
            $psr11Container, 'da-contorller2', 'da-action2', $req, $resp
        );
        unset($_SESSION[\SMVCTools\Tests\TestObjects\ChildController::SESSN_PARAM_LOGIN_REDIRECT]);
        $actionLoginResult3 = $controller2->actionLogin();
        
        $action2 = (
                    $prepend_action 
                    && !str_starts_with(mb_strtolower($controller2->getLoginSuccessRedirectAction(), 'UTF-8'), 'action')
                  ) 
                  ? 'action-' : '';
        $expectedRedirectPath3 = $controller2->makeLink(
            "/{$controller2->getLoginSuccessRedirectController()}/{$action2}{$controller2->getLoginSuccessRedirectAction()}"
        );
        
        // Response Object with a redirect uri should not be returned in this scenario
        self::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionLoginResult3);
        self::assertEquals(302, $actionLoginResult3->getStatusCode());
        self::assertEquals($expectedRedirectPath3, $actionLoginResult3->getHeaderLine('Location'));
        
        $controller2->actionLogout(); // logout, clean up
    }


    /**
     * @runInSeparateProcess
     */
    public function testThat_doLogin_WorksAsExpected() {
        
        if( !defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES') ) {

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new \SMVCTools\Tests\TestObjects\ControllerWithPublicDoLogin(
            $psr11Container, 'da-controller', 'da-action', $req, $resp
        );

        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        $success_redirect_path = '/random-path';
        $expected_redirect_path = 
            $_SESSION[\SMVCTools\Tests\TestObjects\ControllerWithPublicDoLogin::SESSN_PARAM_LOGIN_REDIRECT];
        $credentials = [ 'username'=> 'admin', 'password'=> 'admin', ];
        
        //reset the logger first so that only log messages related to the next 
        //call of doLogin are present in the log object.
        $controller->getContainerItem(ContainerKeys::LOGGER)->reset();
        
        $result = $controller->doLoginPublic(
            $controller->getVespulaAuthObject(), $credentials, $success_redirect_path
        );

        ////////////////////////////////////////////////////////////////////////
        // $success_redirect_path should have the value stored in
        // $_SESSION[\SMVCTools\Tests\TestObjects\ControllerWithPublicDoLogin::SESSN_PARAM_LOGIN_REDIRECT]
        ////////////////////////////////////////////////////////////////////////
        self::assertEquals(
            $expected_redirect_path, 
            $success_redirect_path
        );
        
        ////////////////////////////////////////////////////
        // Check that successful login message was returned
        // and that the controller is in a logged in state
        // and that successful login message was logged via
        // the logger
        ////////////////////////////////////////////////////
        self::assertEquals(
            $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_is_valid_msg'),
            $result
        );
        self::assertTrue($controller->isLoggedIn());
        
        // logger contains expected successfully logged in message
        self::assertTrue(
            $this->stringIsContainedInAtLeastOneArrayItem(
                $controller->getContainerItem(ContainerKeys::LOGGER)->getLogEntries(),
                "User `admin` successfully logged in." . PHP_EOL .PHP_EOL
            )
        );
        
        //////////
        // logout
        //////////
        $controller->getVespulaAuthObject()->logout();
        
        //////////////////////////////////////////////
        // Controller should be in a logged out state
        //////////////////////////////////////////////
        self::assertFalse($controller->isLoggedIn());
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        ////////////////////////////////////////////////////////////////////////
        // Try logging in again, this time $success_redirect_path should not
        // be changed because $_SESSION[\SMVCTools\Tests\TestObjects\ControllerWithPublicDoLogin::SESSN_PARAM_LOGIN_REDIRECT]
        // was unset in the previous login above
        ////////////////////////////////////////////////////////////////////////
        $success_redirect_path2 = '/random-path';
        $original_success_redirect_path = $success_redirect_path2;
        $result2 = $controller->doLoginPublic(
            $controller->getVespulaAuthObject(), $credentials, $success_redirect_path
        );
        
        ////////////////////////////////////////////////////////////////////////
        // $success_redirect_path should not have been changed by doLogin 
        ////////////////////////////////////////////////////////////////////////
        self::assertEquals(
            $original_success_redirect_path, 
            $success_redirect_path2
        );
        
        ////////////////////////////////////////////////////
        // Check that successful login message was returned
        // and that the controller is in a logged in state
        ////////////////////////////////////////////////////
        self::assertEquals(
            $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_is_valid_msg'),
            $result2
        );
        self::assertTrue($controller->isLoggedIn());
        
        //////////
        // logout
        //////////
        $controller->getVespulaAuthObject()->logout();
        
        //////////////////////////////////////////////
        // Controller should be in a logged out state
        //////////////////////////////////////////////
        self::assertFalse($controller->isLoggedIn());
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // Try to login with a username and password not in the database
        $bad_credentials = [ 'username'=> 'non-existent-user', 'password'=> 'non-existent-password', ];
        $result3 = $controller->doLoginPublic(
            $controller->getVespulaAuthObject(), $bad_credentials, $success_redirect_path
        );
        
        ///////////////////////////////////////////////////////
        // Check that unsuccessful login message was returned
        // and that the controller is in a logged out state
        //////////////////////////////////////////////////////
        self::assertStringContainsString(
            $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_not_is_valid_msg'),
            $result3
        );
        self::assertStringContainsString(
            '<br>',
            $result3
        );
        self::assertStringContainsString(
            \Vespula\Auth\Adapter\Sql::ERROR_NO_ROWS,
            $result3
        );
        self::assertFalse($controller->isLoggedIn());
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // Call doLogin with an Auth object that always throws a 
        // \Vespula\Auth\Exception whose message string
        // is not in $backendIssues & $usernamePswdMismatchIssues
        // everytime the login method is called on it.
        
        $exceptionThrowingAuth = $this->newVespulaAuth(
            \SMVCTools\Tests\TestObjects\AlwaysThrowExceptionOnLoginAuth::class
        );
        $originalAuth = $controller->getVespulaAuthObject();
        $controller->setVespulaAuthObject($exceptionThrowingAuth);
        
        //reset the logger first so that only log messages related to the next 
        //call of doLogin are present in the log object.
        $controller->getContainerItem(ContainerKeys::LOGGER)->reset();
        
        $result4 = $controller->doLoginPublic(
            $controller->getVespulaAuthObject(), $bad_credentials, $success_redirect_path
        );
        
        // Right error message was returned
        self::assertEquals(
            $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_v_auth_exception_general_msg'),
            $result4
        );
        
        // logger contains expected error message
        self::assertTrue(
            $this->stringIsContainedInAtLeastOneArrayItem(
                $controller->getContainerItem(ContainerKeys::LOGGER)->getLogEntries(),
                \str_replace(
                    '<br>',
                    PHP_EOL,
                    $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_v_auth_exception_general_msg')
                )
            )
        );
        
        // we should not be logged in
        self::assertFalse($controller->isLoggedIn());
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // Call doLogin with an Auth object that always throws a 
        // \Vespula\Auth\Exception whose message string
        // is in $backendIssues everytime the login method is called on it.
        
        $exceptionThrowingAuth->setExceptionMessage('EXCEPTION_LDAP_CONNECT_FAILED');
        
        //reset the logger first so that only log messages related to the next 
        //call of doLogin are present in the log object.
        $controller->getContainerItem(ContainerKeys::LOGGER)->reset();
        
        $result5 = $controller->doLoginPublic(
            $controller->getVespulaAuthObject(), $bad_credentials, $success_redirect_path
        );
        
        // Right error message was returned
        self::assertEquals(
            $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_v_auth_exception_back_end_msg'),
            $result5
        );
        
        // logger contains expected error message
        self::assertTrue(
            $this->stringIsContainedInAtLeastOneArrayItem(
                $controller->getContainerItem(ContainerKeys::LOGGER)->getLogEntries(),
                \str_replace(
                    '<br>',
                    PHP_EOL,
                    $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_v_auth_exception_back_end_msg')
                )
            )
        );
        
        // we should not be logged in
        self::assertFalse($controller->isLoggedIn());
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // Call doLogin with a credentials array not having any username or
        // password keys, leading to a \Vespula\Auth\Exception being thrown
        
        $controller->setVespulaAuthObject($originalAuth);
        
        //reset the logger first so that only log messages related to the next 
        //call of doLogin are present in the log object.
        $controller->getContainerItem(ContainerKeys::LOGGER)->reset();
        
        $credentials_with_no_uname_or_passwd = [];
        
        $result6 = $controller->doLoginPublic(
            $controller->getVespulaAuthObject(), 
            $credentials_with_no_uname_or_passwd, 
            $success_redirect_path
        );
        
        // Right error message was returned
        self::assertEquals(
            $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_v_auth_exception_user_passwd_msg'),
            $result6
        );
        
        // logger contains expected error message
        self::assertTrue(
            $this->stringIsContainedInAtLeastOneArrayItem(
                $controller->getContainerItem(ContainerKeys::LOGGER)->getLogEntries(),
                \str_replace(
                    '<br>',
                    PHP_EOL,
                    $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_v_auth_exception_user_passwd_msg')
                )
            )
        );
        
        // we should not be logged in
        self::assertFalse($controller->isLoggedIn());
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // Call doLogin with an Auth object that throws a general 
        // \Exception whose message string.
        
        $exceptionThrowingAuth->setExceptionMessage('Booo Booo!');
        $exceptionThrowingAuth->setExceptionClass(\Exception::class);
        $controller->setVespulaAuthObject($exceptionThrowingAuth);
        
        //reset the logger first so that only log messages related to the next 
        //call of doLogin are present in the log object.
        $controller->getContainerItem(ContainerKeys::LOGGER)->reset();
        
        $result7 = $controller->doLoginPublic(
            $controller->getVespulaAuthObject(), $bad_credentials, $success_redirect_path
        );
        
        // Right error message was returned
        self::assertEquals(
            $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_exception_msg'),
            $result7
        );
        
        // logger contains expected error message
        self::assertTrue(
            $this->stringIsContainedInAtLeastOneArrayItem(
                $controller->getContainerItem(ContainerKeys::LOGGER)->getLogEntries(),
                \str_replace(
                    '<br>',
                    PHP_EOL,
                    $controller->getContainerItem(ContainerKeys::LOCALE_OBJ)->gettext('base_controller_do_login_auth_exception_msg')
                )
            )
        );
        
        // we should not be logged in
        self::assertFalse($controller->isLoggedIn());
    }
    
    protected function stringIsContainedInAtLeastOneArrayItem(array $haystacks, string $needle): bool {
        
        $contains = false;
        
        foreach ($haystacks as $haystack) {
            
            if(str_contains($haystack, $needle)) {
                
                $contains = true;
                break;
            }
        }
        
        return $contains;
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testThat_actionLogout_WorksAsExpected() {
        
        if(!defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES')){

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        
        if(
            session_status() === PHP_SESSION_ACTIVE
            && isset($_SESSION[BaseController::SESSN_PARAM_LOGIN_REDIRECT])
        ) {
            unset($_SESSION[BaseController::SESSN_PARAM_LOGIN_REDIRECT]);
        }
        
        $authClass = \SMVCTools\Tests\TestObjects\IsAnonAlwaysFalseAuth::class;
        $controller->setVespulaAuthObject($this->newVespulaAuth($authClass));
        
        // Test that when false is supplied as the value for the $show_status_on_completion
        // parameter of actionLogout & the controller's auth object returns false 
        // when isAnon() is called on it after logout() is called on the auth object, 
        // that the uri that the returned response object contains has either 
        // login-status or action-login-status in the action segment of the 
        // uri path.
        // 
        // No controller was specified in the request object's uri & no controller
        // was passed to the controller object's constructor so the controller
        // calculated will be the default base-controller. We unset the stored
        // login redirect url in the session before callling actionLogout.
        
        $actionResult = $controller->actionLogout(false);
        self::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionResult);
        self::assertEquals(302, $actionResult->getStatusCode());
        self::assertTrue($actionResult->hasHeader('Location'));
        
        if(SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES) {
            
            self::assertEquals("{$controller->getAppBasePath()}/base-controller/login-status", $actionResult->getHeaderLine('Location'));
            
        } else {
            
            self::assertEquals("{$controller->getAppBasePath()}/base-controller/action-login-status", $actionResult->getHeaderLine('Location'));
        }
        
        ////////////////////////////////////////////////////////////////////////
        
        // Test that when false is supplied as the value for the $show_status_on_completion
        // parameter of actionLogout & the controller's auth object returns true when
        // isAnon() is called on it after logout() is called on the auth object, 
        // that the uri that the returned response object contains has either login 
        // or action-login in the action segment of the uri path.
        // 
        // No controller was specified in the request object's uri & no controller
        // was passed to the controller object's constructor so the controller
        // calculated will be the default base-controller. We unset the stored
        // login redirect url in the session before callling actionLogout.
        
        $controller2 = new BaseController(
            $psr11Container, 'da-shizzle-for-rizzle-controller', '', $req, $resp
        );
        
        if(
            session_status() === PHP_SESSION_ACTIVE
            && isset($_SESSION[BaseController::SESSN_PARAM_LOGIN_REDIRECT])
        ) {
            unset($_SESSION[BaseController::SESSN_PARAM_LOGIN_REDIRECT]);
        }
        
        $actionResult2 = $controller2->actionLogout(false);
        self::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionResult2);
        self::assertEquals(302, $actionResult2->getStatusCode());
        self::assertTrue($actionResult2->hasHeader('Location'));
        
        if(SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES) {
            
            self::assertEquals("{$controller2->getAppBasePath()}/da-shizzle-for-rizzle-controller/login", $actionResult2->getHeaderLine('Location'));
            
        } else {
            
            self::assertEquals("{$controller2->getAppBasePath()}/da-shizzle-for-rizzle-controller/action-login", $actionResult2->getHeaderLine('Location'));
        }
        
        ////////////////////////////////////////////////////////////////////////
        
        // Test that when true is supplied as the value for the $show_status_on_completion
        // parameter of actionLogout & the controller's auth object returns true when
        // isAnon() is called on it after logout() is called on the auth object, 
        // that the uri that the returned response object contains has either 
        // login or action-login in the action segment of the uri path.
        // 
        // No controller was specified in the request object's uri but the controller
        // 'da-shizzle-for-rizzle-controller' was passed to the controller object's 
        // constructor. We unset the stored login redirect url in the session before 
        // callling actionLogout.
        
        $controller3 = new BaseController(
            $psr11Container, 'da-shizzle-for-rizzle-controller', '', $req, $resp
        );
        
        $_POST['username'] = 'admin';
        $_POST['password'] = 'admin';
        $controller3->setRequest($controller3->getRequest()->withMethod('POST'));
        $controller3->actionLogin();
        
        self::assertTrue($controller3->isLoggedIn());
        
        if(
            session_status() === PHP_SESSION_ACTIVE
            && isset($_SESSION[BaseController::SESSN_PARAM_LOGIN_REDIRECT])
        ) {
            unset($_SESSION[BaseController::SESSN_PARAM_LOGIN_REDIRECT]);
        }
        
        // reset the logger first so that only log messages related to the next 
        // call of actionLogout are present in the log object.
        $controller3->getContainerItem(ContainerKeys::LOGGER)->reset();
        
        $controller3->setRequest($controller3->getRequest()->withMethod('GET'));
        $actionResult3 = $controller3->actionLogout(true);
        self::assertFalse($controller3->isLoggedIn());
        
        // logger contains expected successfully logged out message
        self::assertTrue(
            $this->stringIsContainedInAtLeastOneArrayItem(
                $controller3->getContainerItem(ContainerKeys::LOGGER)->getLogEntries(),
                "User `admin` successfully logged out" . PHP_EOL .PHP_EOL
            )
        );
        
        self::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionResult3);
        self::assertEquals(302, $actionResult3->getStatusCode());
        self::assertTrue($actionResult3->hasHeader('Location'));
        
        if(SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES) {
            
            self::assertEquals("{$controller3->getAppBasePath()}/da-shizzle-for-rizzle-controller/login-status", $actionResult3->getHeaderLine('Location'));
            
        } else {
            
            self::assertEquals("{$controller3->getAppBasePath()}/da-shizzle-for-rizzle-controller/action-login-status", $actionResult3->getHeaderLine('Location'));
        }
        
        ////////////////////////////////////////////////////////////////////////
        
        // Test that when true is supplied as the value for the $show_status_on_completion
        // parameter of actionLogout & the controller's auth object returns true when
        // isAnon() is called on it after logout() is called on the auth object, 
        // that the uri that the returned response object contains has the redirect
        // url stored in session.
        // 
        // No controller was specified in the request object's uri but the controller
        // 'da-shizzle-for-rizzle-controller' was passed to the controller object's 
        // constructor. We DON'T unset the stored login redirect url in the session before 
        // callling actionLogout.
        
        $controller4 = new BaseController(
            $psr11Container, 'da-shizzle-for-rizzle-controller', '', $req, $resp
        );
        
        $actionResult4 = $controller4->actionLogout(true);
        self::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionResult4);
        self::assertEquals(302, $actionResult4->getStatusCode());
        self::assertTrue($actionResult4->hasHeader('Location'));
        
        if(SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES) {
            
            self::assertEquals("{$controller4->getAppBasePath()}/da-shizzle-for-rizzle-controller/login-status", $actionResult4->getHeaderLine('Location'));
            
        } else {
            
            self::assertEquals("{$controller4->getAppBasePath()}/da-shizzle-for-rizzle-controller/action-login-status", $actionResult4->getHeaderLine('Location'));
        }
        ////////////////////////////////////////////////////////////////////////
        
        // Test that when false is supplied as the value for the $show_status_on_completion
        // parameter of actionLogout & the controller's auth object returns true when
        // isAnon() is called on it after logout() is called on the auth object, 
        // that the uri that the returned response object contains has the redirect
        // url stored in session.
        // 
        // No controller was specified in the request object's uri but the controller
        // 'da-shizzle-for-rizzle-controller' was passed to the controller object's 
        // constructor. We DON'T unset the stored login redirect url in the session before 
        // callling actionLogout.
        
        $controller5 = new BaseController(
            $psr11Container, 'da-shizzle-for-rizzle-controller', '', $req, $resp
        );
        
        $actionResult5 = $controller5->actionLogout(false);
        self::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $actionResult5);
        self::assertEquals(302, $actionResult5->getStatusCode());
        self::assertTrue($actionResult5->hasHeader('Location'));
        self::assertEquals(
            $_SESSION[BaseController::SESSN_PARAM_LOGIN_REDIRECT], 
            $actionResult5->getHeaderLine('Location')
        ); // the uri that the returned response object contains has the redirect url stored in session
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testThat_actionLoginStatus_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );        
        
        $auth = $controller->getVespulaAuthObject();
        $credentials = [ 'username'=> 'admin', 'password'=> 'admin', ];
        $test_params = [
            \Vespula\Auth\Auth::ANON => 'You are not logged in.',
            \Vespula\Auth\Auth::IDLE => 'Your session was idle for too long. Please log in again.',
            \Vespula\Auth\Auth::EXPIRED => 'Your session has expired. Please log in again.',
            \Vespula\Auth\Auth::VALID => 'You are still logged in.',
            'Unknown' => 'Unknown session status.',
        ];
        
        if(!defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES')){

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        foreach ($test_params as $auth_status => $status_message_in_rendered_view) {
            
            $auth->getSession()->setStatus($auth_status);
            $action_result = $controller->actionLoginStatus();
            self::assertStringContainsString($status_message_in_rendered_view, $action_result);
            self::assertStringContainsString('<br>'.nl2br(sMVC_DumpAuthinfo($auth)), $action_result);
        }
        
        ////////////////////////////////////////////////////////////////////////
        // Login & check that the page returned contains expected html elements
        ////////////////////////////////////////////////////////////////////////
        $auth->login($credentials);
        $action_result2 = $controller->actionLoginStatus();
        
        $login_action_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/login");
        $logout_action_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/logout/1");
        $login_status_action_path = $controller->makeLink("/{$controller->getControllerNameFromUri()}/login-status");
        
        self::assertStringNotContainsString("<p> <a href=\"{$login_action_path}\">Log in</a> </p>", $action_result2);
        self::assertStringContainsString(
            "<a href=\"{$login_status_action_path}\">Check Login Status</a>", 
            $action_result2
        );
        self::assertStringContainsString(
            "<form action=\"{$logout_action_path}\" method=\"post\">", 
            $action_result2
        );
        self::assertStringContainsString(
            '<input type="submit" value="Logout">', 
            $action_result2
        );
        self::assertStringContainsString(
            '</form>', 
            $action_result2
        );
        self::assertStringContainsString(
            'Layout: View: ', 
            $action_result2
        );
        
        ////////////////////////////////////////////////////////////////////////
        // Logout & check that the page returned contains expected html elements
        ////////////////////////////////////////////////////////////////////////
        $auth->logout();
        $action_result3 = $controller->actionLoginStatus();
        
        self::assertStringContainsString("<p> <a href=\"{$login_action_path}\">Login</a> </p>", $action_result3);
        self::assertStringNotContainsString(
            "<a href=\"{$login_status_action_path}\">Check Login Status</a>", 
            $action_result3
        );
        self::assertStringNotContainsString(
            "<form action=\"{$logout_action_path}\" method=\"post\">", 
            $action_result3
        );
        self::assertStringNotContainsString(
            '<input type="submit" value="Logout">', 
            $action_result3
        );
        self::assertStringNotContainsString(
            '</form>', 
            $action_result3
        );
        self::assertStringContainsString(
            'Layout: View: ', 
            $action_result3
        );
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testThat_isLoggedIn_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );        
        
        $auth = $controller->getVespulaAuthObject();
        $credentials = [ 'username'=> 'admin', 'password'=> 'admin', ];
        
        $auth->login($credentials);
        self::assertTrue($controller->isLoggedIn());
        
        $auth->logout();
        self::assertFalse($controller->isLoggedIn());
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testThat_getResponseObjForLoginRedirectionIfNotLoggedIn_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new \SMVCTools\Tests\TestObjects\ChildController(
            $psr11Container, 'da-controller', 'da-action', $req, $resp
        );
        $controller->is_logged_in = true;
        self::assertFalse($controller->getResponseObjForLoginRedirectionIfNotLoggedIn());
        
        if( !defined('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES') ) {

            define('SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES', true );
        }
        
        $controller->is_logged_in = false;
        $result = $controller->getResponseObjForLoginRedirectionIfNotLoggedIn();
        self::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result);
        self::assertEquals(302, $result->getStatusCode());
        self::assertTrue($result->hasHeader('Location'));
        
        if(SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES) {
            
            self::assertEquals("{$controller->getAppBasePath()}/da-controller/login", $result->getHeaderLine('Location'));
            
        } else {
            
            self::assertEquals("{$controller->getAppBasePath()}/da-controller/action-login", $result->getHeaderLine('Location'));
        }
        
        ////////////////////////////////////////////////////////////////////////
        // no controller no action specified in the URI & constructor
        ////////////////////////////////////////////////////////////////////////
        $controller2 = new BaseController( $psr11Container, '', '', $req, $resp);
        $result2 = $controller2->getResponseObjForLoginRedirectionIfNotLoggedIn();
        self::assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $result2);
        self::assertEquals(302, $result2->getStatusCode());
        self::assertTrue($result2->hasHeader('Location'));
        
        if(SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES) {
            
            self::assertEquals("{$controller2->getAppBasePath()}/base-controller/login", $result2->getHeaderLine('Location'));
            
        } else {
            
            self::assertEquals("{$controller2->getAppBasePath()}/base-controller/action-login", $result2->getHeaderLine('Location'));
        }
    }
    
    public function testThat_preAction_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );        
        self::assertSame($resp, $controller->preAction());
    }
    
    public function testThat_postAction_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $resp2 = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );        
        self::assertSame($resp2, $controller->postAction($resp2));
    }
    
    public function testThat_getContainerItem_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );        
        self::assertSame($psr11Container->get(ContainerKeys::APP_SETTINGS), $controller->getContainerItem(ContainerKeys::APP_SETTINGS));
        self::assertSame($psr11Container->get(ContainerKeys::NAMESPACES_4_CONTROLLERS), $controller->getContainerItem(ContainerKeys::NAMESPACES_4_CONTROLLERS));
        
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
    
    public function testThat_GetSetLogger_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        self::assertSame($psr11Container->get(ContainerKeys::LOGGER), $controller->getLogger());
        
        $nu_logger = new \SMVCTools\Tests\TestObjects\InMemoryLogger();
        $controller->setLogger($nu_logger);
        self::assertSame($nu_logger, $controller->getLogger());
    }
    
    public function testThat_GetSetVespulaLocale_WorksAsExpected() {
        
        $req = $this->newRequest('http://google.com/');
        $resp = $this->newResponse();
        $psr11Container = $this->getContainer();
        
        $controller = new BaseController(
            $psr11Container, '', '', $req, $resp
        );
        self::assertSame($psr11Container->get(ContainerKeys::LOCALE_OBJ), $controller->getVespulaLocale());
        
        $nu_locale = new \Vespula\Locale\Locale('fr_CA');
        $controller->setVespulaLocale($nu_locale);
        self::assertSame($nu_locale, $controller->getVespulaLocale());
    }
}
