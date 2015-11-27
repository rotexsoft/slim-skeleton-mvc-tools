<?php
namespace Slim3MvcTools\Controllers;

use \Psr\Http\Message\ServerRequestInterface,
    \Psr\Http\Message\ResponseInterface;

/**
 * 
 * Description of BaseController
 *
 * @author Rotimi Adegbamigbe
 * 
 */
class BaseController
{
    /**
     *
     * A Slim App object containing, Request, Response and other Environment
     * information for each request sent to this controller or any of its
     * sub-classes.
     * 
     * @var \Slim\Slim
     * 
     */
    protected $app;

    /**
     * 
     * View object for rendering layout files. 
     *
     * @var \Rotexsoft\Renderer
     *  
     */
    protected $layout_renderer;
    
    /**
     * 
     * View object for rendering view files associated with controller actions. 
     *
     * @var \Rotexsoft\Renderer
     *  
     */
    protected $view_renderer;
        
    /**
     *
     * The action section of the url. 
     * 
     * It should be set to an empty string if the action was not specified via the url
     * 
     * Eg. http://localhost/slim3-skeleton-mvc-app/public/base-controller/action-index
     * will result in $this->action_name_from_uri === 'action-index'
     * 
     * http://localhost/slim3-skeleton-mvc-app/public/base-controller/
     * will result in $this->action_name_from_uri === ''
     * 
     * @var string 
     * 
     */
    public $action_name_from_uri;
    
    /**
     *
     * The controller section of the url.
     * 
     * It should be set to an empty string if the controller was not specified via the url
     * 
     * Eg. http://localhost/slim3-skeleton-mvc-app/public/base-controller/action-index
     * will result in $this->controller_name_from_uri === 'base-controller'
     * 
     * http://localhost/slim3-skeleton-mvc-app/public/
     * will result in $this->controller_name_from_uri === ''
     * 
     * @var string 
     * 
     */
    public $controller_name_from_uri;

    //////////////////////////////////
    // Session Parameter keys
    //////////////////////////////////
    const SESSN_PARAM_LOGIN_REDIRECT = 'login_redirect_path';
    
    //////////////////////////////////
    // Get Parameter keys
    //////////////////////////////////
    
    
    //////////////////////////////////
    // Post Parameter keys
    //////////////////////////////////
    
    
    //////////////////////////////////
    // Get and Post Parameter keys
    //////////////////////////////////
    
    /**
     * 
     * 
     * 
     * @param \Slim\App $app
     * @param string $controller_name_from_uri
     * @param string $action_name_from_uri
     * 
     */
    public function __construct(\Slim\App $app, $controller_name_from_uri, $action_name_from_uri) {
        
        $this->app = $app;
        $this->action_name_from_uri = $action_name_from_uri;
        $this->controller_name_from_uri = $controller_name_from_uri;

        $this->view_renderer = $this->app->getContainer()->get('new_view_renderer');
        $this->layout_renderer = $this->app->getContainer()->get('new_layout_renderer');
    }
    
    /**
     * 
     * Executes a PHP file and returns its output as a string. This file is 
     * supposed to contain the layout template of your site.
     * 
     * @param string $file_name name of the file (including extension eg. `read.php`) 
     *                          containing valid php to be executed and returned as
     *                          string.
     * @param array $data an array of data to be passed to the layout file. Each
     *                    key in this array is automatically converted to php
     *                    variables accessible in the layout file. 
     *                    Eg. passing ['content'=>'yabadabadoo'] to this method 
     *                    will result in a variable named $content (with a 
     *                    value of 'yabadabadoo') being available in the layout
     *                    file (i.e. the file named $file_name).
     * @return string
     * 
     */
    public function renderLayout( $file_name, array $data = ['content'=>'Content should be placed here!'] ) {
        
        return $this->layout_renderer->renderToString($file_name, $data);
    }
    
    /**
     * 
     * Executes a PHP file and returns its output as a string. This file is 
     * supposed to contain the output markup (usually html) for the current 
     * controller action method being executed.
     * 
     * @param string $file_name name of the file (including extension eg. `read.php`) 
     *                          containing valid php to be executed and returned as
     *                          string.
     * @param array $data an array of data to be passed to the view file. Each
     *                    key in this array is automatically converted to php
     *                    variables accessible in the view file. 
     *                    Eg. passing ['content'=>'yabadabadoo'] to this method 
     *                    will result in a variable named $content (with a 
     *                    value of 'yabadabadoo') being available in the view
     *                    file (i.e. the file named $file_name).
     * @return string
     * 
     */
    public function renderView( $file_name, array $data = [] ) {

        return $this->view_renderer->renderToString($file_name, $data);
    }
    
    public function actionIndex() {
        
        //get the contents of the view first
        $view_str = $this->renderView('index.php');
        
        return $this->renderLayout( 'main-template.php', ['content'=>$view_str] );
    }
    
    public function actionLogin() {

        $success_redirect_path = '';
        $using_default_redirect = false;
        $request_obj = $this->app->getContainer()->get('request');
        
        if(
            session_status() === PHP_SESSION_ACTIVE 
            && isset($_SESSION)
            && isset($_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT])
        ) {
            //there is an active session with a redirect url stored in it
            $success_redirect_path = $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT];
        }
        
        if( empty($success_redirect_path) ) {
            
            $using_default_redirect = true;
            
            $controller = $this->controller_name_from_uri;
            
            if( empty($controller) ) {
                
                $controller = 'base-controller';
            }
            
            $prepend_action = !S3MVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;
            $action = ($prepend_action) ? 'action-' : '';
            $success_redirect_path = "{$controller}/{$action}login-status";
        }
        
        $data_4_login_view = [
            'controller_object' => $this, 'error_message' => '', 
            'username' => '', 'password' => '',
        ];
        
        if( strtoupper($request_obj->getMethod()) === 'GET' ) {

            //show login form
            //get the contents of the view first
            $view_str = $this->renderView('login.php', $data_4_login_view);
            
            return $this->renderLayout('main-template.php', ['content' => $view_str]);
            
        } else {
            
            //this is a POST request, process login
            $auth = $this->app->getContainer()->get('vespula_auth'); //get the auth object
            $username = s3MVC_GetSuperGlobal('post', 'username');
            $password = s3MVC_GetSuperGlobal('post', 'password');
            
            $error_msg = '';
            
            if( empty($username) ) {
                
                $error_msg .= "The 'username' field is empty.";
            } 
            
            if( empty($password) ) {
                
                $error_msg .= (empty($error_msg))? '' : '<br>';
                $error_msg .= "The 'password' field is empty.";
            }
            
            if( empty($error_msg) ) {
                
                $credentials = [
                    'username'=> filter_var($username, FILTER_SANITIZE_STRING), 
                    'password'=> $password, //Not sanitizing this. Sanitizing or
                                            //validating passwords should be app
                                            //specific. For example an app can be 
                                            //setup to allow only alphanumeric 
                                            //passwords with a specific list of 
                                            //allowed special characters.
                ];

                $auth->login($credentials); //try to login

                if( $auth->isValid() ) {

                    $msg = "You are now logged into a new session.";

                    if( 
                        !$using_default_redirect 
                        && session_status() === PHP_SESSION_ACTIVE 
                        && isset($_SESSION)
                        && isset($_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT])
                    ) {
                        //redirect url must have been read from the session
                        //remove it from the session, since the login was 
                        //successful and we have already read the value.
                        unset($_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT]);
                    }

                } else {

                    $msg = 'Login Failed!<br>' . $auth->getAdapter()->getError();
                }
                
            } else {
                
                $msg = $error_msg;
            }
            
            if( s3MVC_GetCurrentAppEnvironment() === S3MVC_APP_ENV_DEV ) {
                
                $msg .= '<br>'.nl2br(s3MVC_DumpAuthinfo($auth));
            }

            if( $auth->isValid() ) {
                
                if( strpos($success_redirect_path, s3MVC_GetBaseUrlPath()) === false ) {
                    
                    //prepend base path
                    $success_redirect_path = 
                        s3MVC_GetBaseUrlPath().'/'.ltrim($success_redirect_path, '/');
                }

                //re-direct
                return $this->app
                            ->getContainer()
                            ->get('response')
                            ->withHeader('Location', $success_redirect_path);
            } else {
                
                //re-display login form with error messages
                $data_4_login_view['error_message'] = $msg;
                $data_4_login_view['username'] = $username;
                $data_4_login_view['password'] = $password;
                
                //get the contents of the view first
                $view_str = $this->renderView('login.php', $data_4_login_view);
                
                return $this->renderLayout('main-template.php', ['content'=>$view_str]);
            }
        }
    }
    
    /**
     * 
     * @param mixed $show_status_on_completion any value that evaluates to true or false.
     *                                         When the value is true, the user will be 
     *                                         redirected to actionLoginStatus(). When it
     *                                         is false, the user will be redirected to 
     *                                         actionLogin()
     * @return type
     */
    public function actionLogout($show_status_on_completion = false) {
        
        $this->app->getContainer()->get('vespula_auth')->logout(); //logout
                
        $prepend_action = !S3MVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;
        $action = ($prepend_action) ? 'action-' : '';
        $actn = ($show_status_on_completion) ? $action.'login-status' : $action.'login';
        
        $controller = $this->controller_name_from_uri;

        if( empty($controller) ) {

            $controller = 'base-controller';
        }
        
        $redirect_path = s3MVC_GetBaseUrlPath() . "/{$controller}/{$actn}";
 
        //re-direct
        return $this->app->getContainer()
                         ->get('response')
                         ->withHeader('Location', $redirect_path);
    }
    
    /**
     * 
     * 
     * 
     */
    public function actionLoginStatus() {

        $msg = '';
            
        //Just get the current login status
        $auth = $this->app->getContainer()->get('vespula_auth');

        switch (true) {

            case $auth->isAnon():
                
                $msg = "You are not logged in.";
                break;
            
            case $auth->isIdle():
                
                $msg = "Your session was idle for too long. Please log in again.";
                break;
            
            case $auth->isExpired():
                
                $msg = "Your session has expired. Please log in again.";
                break;
            
            case $auth->isValid():
                $msg = "You are still logged in.";
                break;
            
            default:
                $msg =  "You have an unknown status.";
                break;
        }

        if( s3MVC_GetCurrentAppEnvironment() === S3MVC_APP_ENV_DEV ) {

            $msg .= '<br>'.nl2br(s3MVC_DumpAuthinfo($auth));
        }

        //get the contents of the view first
        $view_str = $this->renderView('login-status.php', ['message'=>$msg, 'is_logged_in'=>$this->isLoggedIn(), 'controller_object'=>$this]);
        
        return $this->renderLayout('main-template.php', ['content'=>$view_str]);
    }

    /**
     * 
     * Force 404 notFound from within action methods in your controller.
     * For example if a database record could not be retrieved, you can force a
     * notFound response.
     * 
     * @param ServerRequestInterface $req a request object
     * @param ResponseInterface $res a response object
     * @param string $_404_page_content a string containing the html to display as the 404 page.
     *                                  If this string contains a value other than the default value,
     *                                  render it as the 404 page
     * 
     * @return ResponseInterface a response object with the 404 status and 
     *                           appropriate body (eg the html showing the 404 message)
     */
    protected function notFound(ServerRequestInterface $req, ResponseInterface $res, $_404_page_content='Page Not Found') {
        
        $not_found_handler = $this->app->getContainer()->get('notFoundHandler');
        
        if( is_callable($not_found_handler) && $_404_page_content === 'Page Not Found') {
            
            return $not_found_handler($req, $res);    
        } 
        
        //404 handler could not be retrieved from the container
        //manually set the 404
        $not_found_response = $res->withStatus(404);
        $not_found_response->getBody()->write($_404_page_content);
        
        return $not_found_response;
    }
    
    public function isLoggedIn() {
        
        return ($this->app->getContainer()->get('vespula_auth')->isValid() === true);
    }
    
    /**
     * 
     * Return a response object (an instance of \Psr\Http\Message\ResponseInterface)
     * if the user is not logged in (The url the user is currently accessing will be
     * stored in $_SESSION with the key `static::SESSN_PARAM_LOGIN_REDIRECT`. Upon
     * a successful login, the user will be redirected back to the current url in
     * $this->actionLogin().
     * 
     * False is returned if the user is logged in and there is no need to redirect to 
     * the login page. 
     * 
     * @return boolean|\Psr\Http\Message\ResponseInterface
     * 
     */
    protected function getResponseObjForLoginRedirectionIfNotLoggedIn() {
        
        if( !$this->isLoggedIn() ) {
            
            $uri = $this->app->request->getUri();
            $base_path = s3MVC_GetBaseUrlPath();
            $fragment = $uri->getFragment();
            $query = $uri->getQuery();
            $path = $uri->getPath();
            
            $path = $base_path . '/' . ltrim($path, '/');
            
            $curr_url = $path. ( $query ? '?' . $query : '' )
                             . ( $fragment ? '#' . $fragment : '' );
            
            $controller = $this->controller_name_from_uri;

            if( empty($controller) ) {

                $controller = 'base-controller';
            }
            
            $prepend_action = !S3MVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;
            $action = ($prepend_action) ? 'action-login' : 'login';
            $redr_path = s3MVC_GetBaseUrlPath() . "/{$controller}/$action";
                    
            if( session_status() !== PHP_SESSION_ACTIVE ) {
                
                //start a new session
                session_start();
            }

            //store current url in session
            $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT] = $curr_url;

            return $this->app->getContainer()
                             ->get('response')
                             ->withHeader('Location', $redr_path);
        }
        
        return false;
    }
}