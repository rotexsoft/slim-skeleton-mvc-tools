<?php
declare(strict_types=1);

namespace SlimMvcTools\Controllers;

use \Psr\Http\Message\ServerRequestInterface,
    \Psr\Http\Message\ResponseInterface,
    \SlimMvcTools\Utils,
    \SlimMvcTools\Controllers\Exceptions\IncorrectlySetPropertyException,
    \SlimMvcTools\Controllers\Exceptions\ExpectedContainerItemMissingException;

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
     * A container object containing dependencies needed by the controller.
     *
     * @var \Psr\Container\ContainerInterface
     *
     */
    protected $container;

    /**
     *
     * View object for rendering layout files.
     *
     * @var \Rotexsoft\FileRenderer\Renderer
     *
     */
    protected $layout_renderer;

    /**
     *
     * View object for rendering view files associated with controller actions.
     *
     * @var \Rotexsoft\FileRenderer\Renderer
     *
     */
    protected $view_renderer;

    /**
     *
     * An auth object used by the following methods of this class:
     *  - isLoggedIn
     *  - actionLogin
     *  - actionLogout
     *  - actionLoginStatus
     *
     * These methods will throw a \SlimMvcTools\Controllers\Exceptions\IncorrectlySetPropertyException
     * if this object was not set before the method call.
     *
     * @var \Vespula\Auth\Auth
     *
     */
    protected $vespula_auth;


    /**
     *
     * Will be used in actionLogin() to construct the url to redirect to upon successful login,
     * if $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT] is not set.
     *
     * @var string
     */
    protected $login_success_redirect_action = 'login-status';

    /**
     *
     * Will be used in actionLogin() to construct the url to redirect to upon successful login,
     * if $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT] is not set.
     *
     * @var string
     */
    protected $login_success_redirect_controller = 'base-controller';

    /**
     *
     * Request Object
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;
    
    /**
     *
     * Response Object
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     *
     * The action section of the url.
     *
     * It should be set to an empty string if the action was not specified via the url
     *
     * Eg. http://localhost/slim-skeleton-mvc-app/public/base-controller/action-index
     * will result in $this->action_name_from_uri === 'action-index'
     *
     * http://localhost/slim-skeleton-mvc-app/public/base-controller/
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
     * Eg. http://localhost/slim-skeleton-mvc-app/public/base-controller/action-index
     * will result in $this->controller_name_from_uri === 'base-controller'
     *
     * http://localhost/slim-skeleton-mvc-app/public/
     * will result in $this->controller_name_from_uri === ''
     *
     * @var string
     *
     */
    public $controller_name_from_uri;

    /**
     *
     * The full url of the current request e.g. http://someserver.com/controller/action
     *
     * @var string
     *
     */
    public $current_uri;

    /**
     *
     * The name of the layout file that will be rendered by $this->layout_renderer inside
     * $this->renderLayout(..)
     *
     * @var string
     *
     */
    public $layout_template_file_name = 'main-template.php';
    
    //////////////////////////////////
    // Session Parameter keys
    //////////////////////////////////
    const SESSN_PARAM_LOGIN_REDIRECT = 'login_redirect_path';
    
    
    /**
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param string $controller_name_from_uri
     * @param string $action_name_from_uri
     * @param \Psr\Http\Message\ServerRequestInterface $req
     * @param \Psr\Http\Message\ResponseInterface $res
     *
     */
    public function __construct(
        \Psr\Container\ContainerInterface $container, $controller_name_from_uri, $action_name_from_uri,
        \Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res
    ) {
        $this->container = $container;
        $this->request = $req;
        $this->response = $res;
        $this->current_uri = sMVC_UriToString($req->getUri());
        $this->action_name_from_uri = $action_name_from_uri;
        $this->controller_name_from_uri = $controller_name_from_uri;

        if( empty($controller_name_from_uri) || empty($action_name_from_uri) ) {

            // calculate $this->controller_name_from_uri and / or
            // $this->action_name_from_uri if necessary

            $uri_path = ($req->getUri() instanceof \Psr\Http\Message\UriInterface)
                                                        ? $req->getUri()->getPath() : '';

            if( !empty($uri_path) && $uri_path !== '/' && strpos($uri_path, '/') !== false ) {

                if( $uri_path[0] === '/' ) {

                    // remove leading slash /
                    $uri_path = substr($uri_path, 1);
                }

                $uri_path_parts = explode('/', $uri_path);

                if( count($uri_path_parts) >= 1 && empty($controller_name_from_uri) ) {

                    $this->controller_name_from_uri = $uri_path_parts[0];
                }

                if( count($uri_path_parts) >= 2 && empty($action_name_from_uri) ) {

                    $this->action_name_from_uri = $uri_path_parts[1];
                }
            }
        }

        $this->storeCurrentUrlForLoginRedirection();
    }

    /**
     *
     * @throws \SlimMvcTools\Controllers\Exceptions\IncorrectlySetPropertyException
     *
     */
    public function ensureVespulaAuthObjectIsSet() {

        if( !($this->vespula_auth instanceof \Vespula\Auth\Auth) ) {

            try {

                $this->vespula_auth = $this->getContainerItem('vespula_auth');

            } catch (ExpectedContainerItemMissingException $ex) {

                $msg = "ERROR: The `vespula_auth` property of `" . get_class($this) . "`"
                     . " must be set via a call to `" . get_class($this) . '::setVespulaAuthObject(...)` '
                     . " before calling `" . get_class($this) . '::' . __FUNCTION__ . '(...)`.'
                     . PHP_EOL;

                throw new IncorrectlySetPropertyException($msg);
            }
        }
    }

    /**
     *
     *
     *
     * USING SETTER INJECTION AS OPPOSED TO CONSTRUCTOR INJECTION TO AVOID HARD DEPENDENCY ON THE
     * OBJECT BEING SET BY THIS METHOD. USERS OF THIS CLASS SHOULD MAKE SURE THIS SETTER IS
     * CALLED BEFORE CALLING ANY OTHER METHOD IN THIS CLASS THAT RELIES ON THE SET OBJECT.
     *
     * @param \Vespula\Auth\Auth $vespula_auth
     *
     */
    public function setVespulaAuthObject(\Vespula\Auth\Auth $vespula_auth) {

        $this->vespula_auth = $vespula_auth;
    }

    /**
     *
     *
     *
     * USING SETTER INJECTION AS OPPOSED TO CONSTRUCTOR INJECTION TO AVOID HARD DEPENDENCY ON THE
     * OBJECT BEING SET BY THIS METHOD. USERS OF THIS CLASS SHOULD MAKE SURE THIS SETTER IS
     * CALLED BEFORE CALLING ANY OTHER METHOD IN THIS CLASS THAT RELIES ON THE SET OBJECT.
     *
     * @param \Rotexsoft\FileRenderer\Renderer $renderer
     *
     */
    public function setLayoutRenderer(\Rotexsoft\FileRenderer\Renderer $renderer) {

        $this->layout_renderer = $renderer;
    }

    /**
     *
     *
     *
     * USING SETTER INJECTION AS OPPOSED TO CONSTRUCTOR INJECTION TO AVOID HARD DEPENDENCY ON THE
     * OBJECT BEING SET BY THIS METHOD. USERS OF THIS CLASS SHOULD MAKE SURE THIS SETTER IS
     * CALLED BEFORE CALLING ANY OTHER METHOD IN THIS CLASS THAT RELIES ON THE SET OBJECT.
     *
     * @param \Rotexsoft\FileRenderer\Renderer $renderer
     *
     */
    public function setViewRenderer(\Rotexsoft\FileRenderer\Renderer $renderer) {

        $this->view_renderer = $renderer;
    }

    public function getRequest():\Psr\Http\Message\ServerRequestInterface {

        return $this->request;
    }
    
    public function setRequest(\Psr\Http\Message\ServerRequestInterface $request) {

        $this->request = $request;
    }

    public function getResponse(): \Psr\Http\Message\ResponseInterface {

        return $this->response;
    }
    
    public function setResponse(\Psr\Http\Message\ResponseInterface $response) {

        $this->response = $response;
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
     * @throws \SlimMvcTools\Controllers\Exceptions\IncorrectlySetPropertyException
     *
     */
    public function renderLayout( $file_name, array $data = ['content'=>'Content should be placed here!'] ) {

        if( !($this->layout_renderer instanceof \Rotexsoft\FileRenderer\Renderer) ) {

            try {
                $this->layout_renderer = $this->getContainerItem('new_layout_renderer');

            } catch (ExpectedContainerItemMissingException $ex) {

                $msg = "ERROR: The `layout_renderer` property of `" . get_class($this) . "`"
                     . " must be set via a call to `" . get_class($this) . '::setLayoutRenderer(...)` '
                     . " before calling `" . get_class($this) . '::' . __FUNCTION__ . '(...)`.'
                     . PHP_EOL;

                throw new IncorrectlySetPropertyException($msg);
            }
        }

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
     * @throws \SlimMvcTools\Controllers\Exceptions\IncorrectlySetPropertyException
     *
     */
    public function renderView( $file_name, array $data = [] ) {

        if( !($this->view_renderer instanceof \Rotexsoft\FileRenderer\Renderer) ) {

            try {
                $this->view_renderer = $this->getContainerItem('new_view_renderer');

            } catch (ExpectedContainerItemMissingException $ex) {

                $msg = "ERROR: The `view_renderer` property of `" . get_class($this) . "`"
                     . " must be set via a call to `" . get_class($this) . '::setViewRenderer(...)` '
                     . " before calling `" . get_class($this) . '::' . __FUNCTION__ . '(...)`.'
                     . PHP_EOL;

                throw new IncorrectlySetPropertyException($msg);
            }
        }

        $parent_classes = [];
        $parent_class = get_parent_class($this);

        while( $parent_class !== __CLASS__ && !empty($parent_class) ) {

            $parent_classes[] =
                (new \ReflectionClass($parent_class))->getShortName();

            $parent_class = get_parent_class($parent_class);
        }

        //Try to prepend view folder for this controller.
        //It takes precedence over the view folder
        //for the base controller.
        $ds = DIRECTORY_SEPARATOR;
        $path_2_view_files = SMVC_APP_ROOT_PATH.$ds.'src'.$ds.'views'.$ds;

        while ( $parent_class = array_pop($parent_classes) ) {

            $parent_class_folder = \SlimMvcTools\Functions\Str\toDashes($parent_class);

            if(
                !$this->view_renderer->hasPath($path_2_view_files . $parent_class_folder)
                && file_exists($path_2_view_files . $parent_class_folder)
            ) {
                $this->view_renderer->prependPath($path_2_view_files . $parent_class_folder);
            }
        }

        //finally add my view folder
        if(
            !$this->view_renderer->hasPath($path_2_view_files . $this->controller_name_from_uri)
            && file_exists($path_2_view_files . $this->controller_name_from_uri)
        ) {
            $this->view_renderer->prependPath($path_2_view_files . $this->controller_name_from_uri);
        }

        return $this->view_renderer->renderToString($file_name, $data);
    }

    public function actionIndex() {

        //get the contents of the view first
        $view_str = $this->renderView('index.php', ['controller_object'=>$this]);

        return $this->renderLayout( $this->layout_template_file_name, ['content'=>$view_str] );
    }

    /**
     * Display an HTML table containing all the potential MVC routes in the application
     *
     * @param bool $onlyPublicMethodsPrefixedWithAction true to include only public methods prefixed with `action`
     *                                                  or false to include all public methods
     * @return \Psr\Http\Message\ResponseInterface|string
     */
    public function actionRoutes($onlyPublicMethodsPrefixedWithAction=true) {

        $resp = $this->getResponseObjForLoginRedirectionIfNotLoggedIn();

        if($resp !== false) {

            return $resp;
        }

        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '0');

        $view_str = $this->renderView(
            'controller-classes-by-action-methods-report.php',
            ['onlyPublicMethodsPrefixedWithAction'=> ((bool)$onlyPublicMethodsPrefixedWithAction)]
        );

        return $this->renderLayout( $this->layout_template_file_name, ['content'=>$view_str] );
    }

    public function actionLogin() {

        $request_obj = $this->request;

        $data_4_login_view = [
            'controller_object' => $this, 'error_message' => '', 'username' => '',
            'password' => ''
        ];

        if( strtoupper($request_obj->getMethod()) === 'GET' ) {

            //show login form
            //get the contents of the view first
            $view_str = $this->renderView('login.php', $data_4_login_view);

            return $this->renderLayout( $this->layout_template_file_name, ['content' => $view_str]);

        } else {

            //this is a POST request, process login
            $controller = $this->login_success_redirect_controller ?: 'base-controller';

            $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;
            $action = ($prepend_action) ? 'action-' : '';
            $success_redirect_path =
                "{$controller}/{$action}{$this->login_success_redirect_action}";

            $this->ensureVespulaAuthObjectIsSet();
            $auth = $this->vespula_auth; //get the auth object
            $username = sMVC_GetSuperGlobal('post', 'username');
            $password = sMVC_GetSuperGlobal('post', 'password');

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
                
                try {

                    $auth->login($credentials); //try to login

                    if( $auth->isValid() ) {

                        $msg = "You are now logged into a new session.";

                        //since we are successfully logged in, resume session if any
                        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

                        if( isset($_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT]) ) {

                            //there is an active session with a redirect url stored in it
                            $success_redirect_path = $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT];

                            //since login is successful remove stored redirect url, 
                            //it has served its purpose & we'll be redirecting now.
                            unset($_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT]);
                        }

                    } else {

                        $msg = 'Login Failed!<br>' . $auth->getAdapter()->getError();
                    }
                } catch (\Vespula\Auth\Exception $vaExc) {
                    
                    $backendIssues = [
                        'EXCEPTION_LDAP_CONNECT_FAILED', 
                        'Could not bind to basedn',
                    ];
                    
                    $usernamePswdMismatchIssues = [
                        'The LDAP DN search failed'
                    ];
                    
                    $msg = "Login Failed!<br>Login server is busy right now."
                         . "<br>Please try again later.";
                    
                    if(\in_array($vaExc->getMessage(), $backendIssues)) {
                        
                        $msg = "Login Failed!<br>Can't connect to login server right now."
                             . "<br>Please try again later.";
                    }
                    
                    if(\in_array($vaExc->getMessage(), $usernamePswdMismatchIssues)) {
                        
                        $msg = "Login Failed!<br>Incorrect User Name and Password combination"
                             . "<br>Please try again.";
                    }
                    
                    $this->container->has('logger')
                        && ( $this->container->get('logger') instanceof \Psr\Log\LoggerInterface )
                        && $this->container->get('logger')
                                ->error( 
                                    \str_replace('<br>', PHP_EOL, $msg)
                                    . Utils::getThrowableAsStr($vaExc)
                                );
                    
                } catch(\Exception $basExc) {
                    
                    $msg = "Login Failed!"
                         . "<br>Please contact the site administrator.";
                    
                    $this->container->has('logger')
                        && ( $this->container->get('logger') instanceof \Psr\Log\LoggerInterface )
                        && $this->container->get('logger')
                                ->error(
                                    \str_replace('<br>', PHP_EOL, $msg)
                                    . Utils::getThrowableAsStr($basExc)
                                );
                }

            } else {

                $msg = $error_msg;
            }

            if( sMVC_GetCurrentAppEnvironment() === SMVC_APP_ENV_DEV ) {

                $msg .= '<br>'.nl2br(sMVC_DumpAuthinfo($auth));
            }

            if( $auth->isValid() ) {

                if( sMVC_GetBaseUrlPath().'' === '' || strpos($success_redirect_path, sMVC_GetBaseUrlPath()) === false ) {

                    //prepend base path
                    $success_redirect_path =
                        sMVC_GetBaseUrlPath().'/'.ltrim($success_redirect_path, '/');
                }

                //re-direct
                return $this->response->withStatus(302)->withHeader('Location', $success_redirect_path);
            } else {

                //re-display login form with error messages
                $data_4_login_view['error_message'] = $msg;
                $data_4_login_view['username'] = $username;
                $data_4_login_view['password'] = $password;

                //get the contents of the view first
                $view_str = $this->renderView('login.php', $data_4_login_view);

                return $this->renderLayout( $this->layout_template_file_name, ['content'=>$view_str] );
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

        $this->ensureVespulaAuthObjectIsSet();
        $auth = $this->vespula_auth;
        $auth->logout(); //logout

        if( !$auth->isAnon() ) {

            //logout failed. Definitely redirect to actionLoginStatus
            $show_status_on_completion = true;
        }

        $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;
        $action = ($prepend_action) ? 'action-' : '';
        $actn = ($show_status_on_completion) ? $action.'login-status' : $action.'login';

        $controller = $this->controller_name_from_uri;

        if( empty($controller) ) {

            $controller = 'base-controller';
        }

        $redirect_path = sMVC_GetBaseUrlPath() . "/{$controller}/{$actn}";

        if(
            session_status() === PHP_SESSION_ACTIVE
            && isset($_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT])
        ) {
            //there is an active session with a redirect url stored in it
            $redirect_path = $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT];
        }

        //re-direct
        return $this->response->withStatus(302)->withHeader('Location', $redirect_path);
    }

    /**
     *
     *
     *
     *
     */
    public function actionLoginStatus() {

        $msg = '';
        $this->ensureVespulaAuthObjectIsSet();
        $auth = $this->vespula_auth;

        //Just get the current login status
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

        if( sMVC_GetCurrentAppEnvironment() === SMVC_APP_ENV_DEV ) {

            $msg .= '<br>'.nl2br(sMVC_DumpAuthinfo($auth));
        }

        //get the contents of the view first
        $view_str = $this->renderView( 'login-status.php', ['message'=>$msg, 'is_logged_in'=>$this->isLoggedIn(), 'controller_object'=>$this] );

        return $this->renderLayout( $this->layout_template_file_name, ['content'=>$view_str] );
    }

    public function isLoggedIn() {

        $this->ensureVespulaAuthObjectIsSet();
        return ($this->vespula_auth->isValid() === true);
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

            $this->storeCurrentUrlForLoginRedirection();

            $controller = $this->controller_name_from_uri;

            if( empty($controller) ) {

                $controller = 'base-controller';
            }

            $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;
            $action = ($prepend_action) ? 'action-login' : 'login';
            $redr_path = sMVC_GetBaseUrlPath() . "/{$controller}/$action";

            return $this->response->withStatus(302)->withHeader('Location', $redr_path);
        }

        return false;
    }

    public function preAction() {

        //Inject some dependencies into the controller
        $this->setLayoutRenderer($this->getContainerItem('new_layout_renderer'));
        $this->setViewRenderer($this->getContainerItem('new_view_renderer'));
        $this->setVespulaAuthObject($this->getContainerItem('vespula_auth'));

        return $this->response;
    }

    public function postAction(\Psr\Http\Message\ResponseInterface $response) {

        return $response;
    }

    protected function storeCurrentUrlForLoginRedirection() {

        if(
            in_array(
                strtolower($this->action_name_from_uri),
                [
                    'login', 'action-login', 'actionlogin', 'action_login',
                    'logout', 'action-logout', 'actionlogout', 'action_logout'
                ]
            )
            || strtolower($this->request->getHeaderLine('X-Requested-With')) === strtolower('XMLHttpRequest') //ajax request
            || ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' ) //ajax request
        ) { return; }

        $uri = $this->request->getUri();
        $base_path = sMVC_GetBaseUrlPath();
        $fragment = $uri->getFragment();
        $query = $uri->getQuery();
        $path = $uri->getPath();

        $path = $base_path . '/' . ltrim($path, '/');
        $curr_url = $path. ( $query ? '?' . $query : '' )
                         . ( $fragment ? '#' . $fragment : '' );

        //start a new session if none exists
        (session_status() !== PHP_SESSION_ACTIVE) && session_start();

        //store current url in session
        $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT] = $curr_url;
    }

    /**
     *
     * @param string $item_key_in_container
     * @return mixed
     *
     * @throws \SlimMvcTools\Controllers\Exceptions\ExpectedContainerItemMissingException
     */
    protected function getContainerItem($item_key_in_container) {

        if( $this->container->has($item_key_in_container) ) {

            return $this->container->get($item_key_in_container);

        } else {

            $msg = "ERROR: The item with the key named `$item_key_in_container` does not exist in"
                 . " the container associated with `" . get_class($this) . "` ."
                 . PHP_EOL;

            throw new ExpectedContainerItemMissingException($msg);
        }
    }

    public function getContainer() {

        return $this->container;
    }
}
