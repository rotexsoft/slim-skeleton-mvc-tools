<?php
declare(strict_types=1);

namespace SlimMvcTools\Controllers;

use \Psr\Http\Message\ServerRequestInterface,
    \Psr\Http\Message\ResponseInterface,
    \SlimMvcTools\Utils;

/**
 *
 * Description of BaseController
 *
 * @author Rotimi Adegbamigbe
 */
class BaseController
{
    /**
     * A container object containing dependencies needed by the controller.
     */
    protected \Psr\Container\ContainerInterface $container;

    /**
     * View object for rendering layout files.
     */
    protected \Rotexsoft\FileRenderer\Renderer $layout_renderer;

    /**
     * View object for rendering view files associated with controller actions.
     */
    protected \Rotexsoft\FileRenderer\Renderer $view_renderer;

    /**
     * An auth object used by the following methods of this class:
     *  - isLoggedIn
     *  - actionLogin
     *  - actionLogout
     *  - actionLoginStatus
     *
     * These methods will throw a \SlimMvcTools\Controllers\Exceptions\IncorrectlySetPropertyException
     * if this object was not set before the method call.
     */
    protected \Vespula\Auth\Auth $vespula_auth;

    /**
     * Will be used in actionLogin() to construct the url to redirect to upon successful login,
     * if $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT] is not set.
     */
    protected string $login_success_redirect_action = 'login-status';

    /**
     * Will be used in actionLogin() to construct the url to redirect to upon successful login,
     * if $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT] is not set.
     */
    protected string $login_success_redirect_controller = 'base-controller';

    /**
     * Request Object 
     */
    protected \Psr\Http\Message\ServerRequestInterface $request;
    
    /**
     * Response Object 
     */
    protected \Psr\Http\Message\ResponseInterface $response;

    /**
     * The action section of the url.
     *
     * It should be set to an empty string if the action was not specified via the url
     *
     * Eg. http://localhost/slim-skeleton-mvc-app/public/base-controller/action-index
     * will result in $this->action_name_from_uri === 'action-index'
     *
     * http://localhost/slim-skeleton-mvc-app/public/base-controller/
     * will result in $this->action_name_from_uri === ''
     */
    protected string $action_name_from_uri = '';
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getActionNameFromUri(): string {
        
        return $this->action_name_from_uri;
    }

    /**
     * The controller section of the url.
     *
     * It should be set to an empty string if the controller was not specified via the url
     *
     * Eg. http://localhost/slim-skeleton-mvc-app/public/base-controller/action-index
     * will result in $this->controller_name_from_uri === 'base-controller'
     *
     * http://localhost/slim-skeleton-mvc-app/public/
     * will result in $this->controller_name_from_uri === ''
     */
    protected string $controller_name_from_uri = '';
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getControllerNameFromUri(): string {
        
        return $this->controller_name_from_uri;
    }

    /**
     * The full url of the current request AS IS from the browser 
     * e.g. http://someserver.com/controller/action[/param1/.../paramN][?var1=val1&...&varN=valN][#some-fragment]
     * @psalm-suppress PossiblyUnusedProperty
     */
    protected string $current_uri;
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getCurrentUri(): string {
        
        return $this->current_uri;
    }
    
    /**
     * The full url of the current request computed in the constructor of this class
     * 
     * This value is more reliable for requests with no controller and/or action specified 
     * in the URL, meaning that the default controller and/or action will be used to service
     * the request represented by the URL.
     * 
     * Still using the url format like the one below:
     * 
     * http[s]://someserver.com/controller/action[/param1/.../paramN][?var1=val1&...&varN=valN][#some-fragment]
     * 
     * @psalm-suppress PossiblyUnusedProperty
     */
    protected string $current_uri_computed;
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getCurrentUriComputed(): string {
        
        return $this->current_uri_computed;
    }
    
    /**
     * The name of the layout file that will be rendered by $this->layout_renderer inside
     * $this->renderLayout(..)
     */
    protected string $layout_template_file_name = 'main-template.php';
    
    //////////////////////////////////
    // Session Parameter keys
    //////////////////////////////////
    public const SESSN_PARAM_LOGIN_REDIRECT = 'login_redirect_path';
    
    /**
     * 
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(
        \Psr\Container\ContainerInterface $container, 
        string $controller_name_from_uri,
        string $action_name_from_uri,
        ServerRequestInterface $req, 
        ResponseInterface $res
    ) {
        $this->container = $container;
        $this->request = $req;
        $this->response = $res;
        $this->current_uri = sMVC_UriToString($req->getUri());
        $this->current_uri_computed = $this->current_uri; // default value, will recompute later below if needed
        $this->action_name_from_uri = ($action_name_from_uri !== '') ? $action_name_from_uri : $this->action_name_from_uri;
        $this->controller_name_from_uri = ($controller_name_from_uri !== '') ? $controller_name_from_uri : $this->controller_name_from_uri;
        
        /** @psalm-suppress MixedAssignment */
        $this->vespula_auth = $this->getContainerItem('vespula_auth');
        
        /** @psalm-suppress MixedAssignment */
        $this->layout_renderer = $this->getContainerItem('new_layout_renderer');
        
        /** @psalm-suppress MixedAssignment */
        $this->view_renderer = $this->getContainerItem('new_view_renderer');
        
        $uri_path = ($req->getUri() instanceof \Psr\Http\Message\UriInterface)
                                                ? $req->getUri()->getPath() : '';
        
        if(
            (
                $this->controller_name_from_uri !== ''
                && !str_contains($uri_path, $this->controller_name_from_uri)
            )
            || 
            (
                $this->action_name_from_uri !== ''
                && !str_contains($uri_path, $this->action_name_from_uri)
            )
        ) {
            // This must be a uri with either no controller & no action
            // or with a controller and no action, meaning that either
            // the default controller and / or the default action will
            // be invoked. This uri could still contain $this->getAppBasePath()
            
            // We need to recompute the current uri to include the 
            // default controller and / or the default action while also
            // preserving the base-path in its current position & other
            // parts of the uri like the query string & fragment
            // The recomputed uri will be stored in $this->current_uri_computed
            // It is assumed that since either both the controller & action
            // or only the action are/is missing from the original uri, there
            // won't be any parameters in the uri path meant to be passed on to
            // the action to be invoked.
            
            // Give $recomputed_uri a default value of the uri from the request
            $recomputed_uri = $req->getUri();
            
            if($uri_path === '/') {
                
                ///////////////////////////////////////////////////
                // no controller, no action, no explicit base path
                ///////////////////////////////////////////////////
                
                $recomputed_uri = 
                    (
                        $this->controller_name_from_uri !== ''
                        && $this->action_name_from_uri !== ''
                    )
                    ? $req->getUri()->withPath("/{$this->controller_name_from_uri}/{$this->action_name_from_uri}")
                    : $req->getUri()->withPath("/{$this->controller_name_from_uri}");
                    
            } elseif (str_contains($uri_path, $this->getAppBasePath())) {
                
                /////////////////////////////////////////////
                // base path is contained in the request uri
                /////////////////////////////////////////////
                
                $recomputed_uri = 
                    (
                        $this->controller_name_from_uri !== ''
                        && $this->action_name_from_uri !== ''
                    )
                    ? 
                    (
                        str_starts_with($this->getAppBasePath(), '/')
                        ? $req->getUri()->withPath("{$this->getAppBasePath()}/{$this->controller_name_from_uri}/{$this->action_name_from_uri}")
                        : $req->getUri()->withPath("/{$this->getAppBasePath()}/{$this->controller_name_from_uri}/{$this->action_name_from_uri}")
                    )
                    :
                    (
                        str_starts_with($this->getAppBasePath(), '/')
                        ? $req->getUri()->withPath("{$this->getAppBasePath()}/{$this->controller_name_from_uri}")
                        : $req->getUri()->withPath("/{$this->getAppBasePath()}/{$this->controller_name_from_uri}")
                    );
            }
            
            $this->current_uri_computed = sMVC_UriToString($recomputed_uri);
        }
        
        if( 
            (($this->controller_name_from_uri === '') || ($this->action_name_from_uri === ''))
            && ( ($uri_path !== '') && ($uri_path !== '/') && (strpos($uri_path, '/') !== false) )
        ) {
            // Calculate $this->controller_name_from_uri and / or
            // $this->action_name_from_uri if necessary
            if( $uri_path[0] === '/' ) {

                // Remove leading slash /
                $uri_path = substr($uri_path, 1);
            }

            $uri_path_parts = explode('/', $uri_path);

            if( count($uri_path_parts) >= 1 && ($this->controller_name_from_uri === '') ) {

                $this->controller_name_from_uri = $uri_path_parts[0];
            }

            if( count($uri_path_parts) >= 2 && ($this->action_name_from_uri === '') ) {

                $this->action_name_from_uri = $uri_path_parts[1];
            }
        }

        $this->storeCurrentUrlForLoginRedirection();
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getVespulaAuthObject(): \Vespula\Auth\Auth {
        
        return $this->vespula_auth;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function setVespulaAuthObject(\Vespula\Auth\Auth $vespula_auth): self {

        $this->vespula_auth = $vespula_auth;
        
        return $this;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getLayoutRenderer(): \Rotexsoft\FileRenderer\Renderer {
        
        return $this->layout_renderer;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function setLayoutRenderer(\Rotexsoft\FileRenderer\Renderer $renderer): self {

        $this->layout_renderer = $renderer;
        
        return $this;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getViewRenderer(): \Rotexsoft\FileRenderer\Renderer {
        
        return $this->view_renderer;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function setViewRenderer(\Rotexsoft\FileRenderer\Renderer $renderer): self {

        $this->view_renderer = $renderer;
        
        return $this;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getRequest():\Psr\Http\Message\ServerRequestInterface {

        return $this->request;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function setRequest(\Psr\Http\Message\ServerRequestInterface $request): self {

        $this->request = $request;
        
        return $this;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getResponse(): \Psr\Http\Message\ResponseInterface {

        return $this->response;
    }
    
    public function setResponse(\Psr\Http\Message\ResponseInterface $response): self {

        $this->response = $response;
        
        return $this;
    }
    
    /** @psalm-suppress MixedInferredReturnType */
    public function getAppBasePath(): string {
        
        /** 
         * @psalm-suppress MixedArrayAccess
         * @psalm-suppress MixedReturnStatement
         */
        return $this->getAppSetting('app_base_path');
    }
    
    /**
     * @return mixed
     */
    public function getAppSetting(string $setting_key) {
 
       /** 
         * @psalm-suppress MixedAssignment
         */
        $settings = $this->getContainerItem('settings');
        
       /** 
         * @psalm-suppress MixedArrayAccess
         */
        return $settings[$setting_key] ?? null; 
    }
    
    public function makeLink(string $path): string {
            
        return rtrim($this->getAppBasePath(), '/')  . '/' . ltrim($path, '/'); 
    }
    
    /**
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
     * 
     * @psalm-suppress MixedInferredReturnType
     *
     */
    public function renderLayout( string $file_name, array $data = ['content'=>'Content should be placed here!'] ): string {

        $self = $this;
        $data['sMVC_MakeLink'] = fn(string $path): string => $self->makeLink($path);
        
        // get new instance
        /** @psalm-suppress MixedAssignment */
        $this->layout_renderer = $this->getContainerItem('new_layout_renderer');
        
        /** 
         * @psalm-suppress MixedReturnStatement
         * @psalm-suppress MixedMethodCall
         */
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
     * 
     * @psalm-suppress MixedInferredReturnType
     */
    public function renderView( string $file_name, array $data = [] ): string {

        $parent_classes = [];
        $parent_class = get_parent_class($this);
        
        // get new instance
        /** @psalm-suppress MixedAssignment */
        $this->view_renderer = $this->getContainerItem('new_view_renderer');

        while( $parent_class !== self::class && ($parent_class !== '' && $parent_class !== false) ) {

            $parent_classes[] =
                (new \ReflectionClass($parent_class))->getShortName();

            $parent_class = get_parent_class($parent_class);
        }

        //Try to prepend view folder for this controller.
        //It takes precedence over the view folder
        //for the base controller.
        $ds = DIRECTORY_SEPARATOR;
        
        /** @psalm-suppress UndefinedConstant */
        $path_2_view_files = SMVC_APP_ROOT_PATH.$ds.'src'.$ds.'views'.$ds;

        while ( $parent_class = array_pop($parent_classes) ) {

            $parent_class_folder = \SlimMvcTools\Functions\Str\toDashes($parent_class);

            /** @psalm-suppress MixedMethodCall */
            if(
                !$this->view_renderer->hasPath($path_2_view_files . $parent_class_folder)
                && file_exists($path_2_view_files . $parent_class_folder)
            ) {
                /** @psalm-suppress MixedMethodCall */
                $this->view_renderer->prependPath($path_2_view_files . $parent_class_folder);
            }
        }

        //finally add my view folder
        /** @psalm-suppress MixedMethodCall */
        if(
            $this->controller_name_from_uri !== ''
            && !$this->view_renderer->hasPath($path_2_view_files . $this->controller_name_from_uri)
            && file_exists($path_2_view_files . $this->controller_name_from_uri)
        ) {
            /** @psalm-suppress MixedMethodCall */
            $this->view_renderer->prependPath($path_2_view_files . $this->controller_name_from_uri);
        }

        $self = $this;
        $data['sMVC_MakeLink'] = fn(string $path): string => $self->makeLink($path);
        
        /** 
         * @psalm-suppress MixedReturnStatement
         * @psalm-suppress MixedMethodCall
         */
        return $this->view_renderer->renderToString($file_name, $data);
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function actionIndex(): string {

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
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function actionRoutes($onlyPublicMethodsPrefixedWithAction=true) {

        $resp = $this->getResponseObjForLoginRedirectionIfNotLoggedIn();

        if($resp instanceof \Psr\Http\Message\ResponseInterface) {

            return $resp;
        }

        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '0');

        /** @psalm-suppress RedundantCastGivenDocblockType */
        $view_str = $this->renderView(
            'controller-classes-by-action-methods-report.php',
            ['onlyPublicMethodsPrefixedWithAction'=> ((bool)$onlyPublicMethodsPrefixedWithAction)]
        );

        return $this->renderLayout( $this->layout_template_file_name, ['content'=>$view_str] );
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface|string
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function actionLogin() {

        $data_4_login_view = [
            'controller_object' => $this, 'error_message' => '', 
            'username' => '', 'password' => ''
        ];

        if( strtoupper($this->request->getMethod()) === 'GET' ) {

            //show login form
            //get the contents of the view first
            $view_str = $this->renderView('login.php', $data_4_login_view);

            return $this->renderLayout( $this->layout_template_file_name, ['content' => $view_str]);

        } else {

            //this is a POST request, process login
            $controller = $this->login_success_redirect_controller ?: 'base-controller';

            // SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES === true
            // means that links generated in this action do not need to be prefixed
            // with action- since when users click on them, the framework will 
            // automatically append action to the resolved method name
            // see \SlimMvcTools\MvcRouteHandler::__invoke(...)
            /** @psalm-suppress UndefinedConstant */
            $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;
            $action = (
                        $prepend_action 
                        && !str_starts_with(mb_strtolower($this->login_success_redirect_action, 'UTF-8'), 'action')
                      ) 
                      ? 'action-' : '';

            $success_redirect_path =
                $this->makeLink("{$controller}/{$action}{$this->login_success_redirect_action}");

            $auth = $this->vespula_auth; //get the auth object

            /** @psalm-suppress MixedAssignment */
            $username = sMVC_GetSuperGlobal('post', 'username');

            /** @psalm-suppress MixedAssignment */
            $password = sMVC_GetSuperGlobal('post', 'password');
            $error_msg = '';

            if( empty($username) ) {
                /** @psalm-suppress MixedOperand */
                $error_msg .= $this->getAppSetting('base_controller_action_login_empty_username_msg');
            }

            if( empty($password) ) {

                $error_msg .= (($error_msg === ''))? '' : '<br>';
                /** @psalm-suppress MixedOperand */
                $error_msg .= $this->getAppSetting('base_controller_action_login_empty_password_msg');
            }

            if( ($error_msg === '') ) {

                $credentials = [
                    'username'=> filter_var($username, FILTER_SANITIZE_STRING),
                    'password'=> $password, //Not sanitizing this. Sanitizing or
                                            //validating passwords should be app
                                            //specific & done during user creation. 
                                            //For example an app can be setup to 
                                            //allow only alphanumeric passwords 
                                            //with a specific list of allowed 
                                            //special characters.
                ];
                $msg = $this->doLogin($auth, $credentials, $success_redirect_path);

            } else {

                $msg = $error_msg;
            }

            /** 
             * @psalm-suppress UndefinedConstant
             * @psalm-suppress UndefinedFunction
             */
            if( sMVC_GetCurrentAppEnvironment() === SMVC_APP_ENV_DEV ) {

                $msg .= '<br>'.nl2br(sMVC_DumpAuthinfo($auth));
            }

            if( $auth->isValid() ) {

                //re-direct
                /** @psalm-suppress MixedArgument */
                return $this->response
                            ->withStatus(302)
                            ->withHeader('Location', $success_redirect_path);
            } else {

                //re-display login form with error messages
                $data_4_login_view['error_message'] = $msg;

                /** @psalm-suppress MixedAssignment */
                $data_4_login_view['username'] = $username;

                /** @psalm-suppress MixedAssignment */
                $data_4_login_view['password'] = $password;

                //get the contents of the view first
                $view_str = $this->renderView('login.php', $data_4_login_view);

                return $this->renderLayout( $this->layout_template_file_name, ['content'=>$view_str] );
            }
        } // if( strtoupper($this->request->getMethod()) === 'GET' ) else {....}
    }
    
    /** @psalm-suppress MixedInferredReturnType */
    protected function doLogin(\Vespula\Auth\Auth $auth, array $credentials, string &$success_redirect_path): string {
        
        $_msg = '';
        
        try {
            $auth->login($credentials); //try to login

            if( $auth->isValid() ) {
                
                /** @psalm-suppress MixedAssignment */
                $_msg = $this->getAppSetting('base_controller_do_login_auth_is_valid_msg');

                //since we are successfully logged in, resume session if any
                if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

                /** @psalm-suppress MixedArrayOffset */
                if( isset($_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT]) ) {

                    //there is an active session with a redirect url stored in it
                    /** @psalm-suppress MixedAssignment */
                    $success_redirect_path = $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT];

                    //since login is successful remove stored redirect url, 
                    //it has served its purpose & we'll be redirecting now.
                    unset($_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT]);
                }

            } else {
                /** @psalm-suppress MixedAssignment */
                $_msg = $this->getAppSetting('base_controller_do_login_auth_not_is_valid_msg');

                /** 
                 * @psalm-suppress UndefinedFunction
                 * @psalm-suppress UndefinedConstant
                 */
                if( sMVC_GetCurrentAppEnvironment() !== SMVC_APP_ENV_PRODUCTION ) {
                    /** @psalm-suppress MixedOperand */
                    $_msg .=  '<br>' . $auth->getAdapter()->getError();
                }
            }
        } catch (\Vespula\Auth\Exception $vaExc) {

            $backendIssues = [
                'EXCEPTION_LDAP_CONNECT_FAILED', 
                'Could not bind to basedn',
                'LDAP extension not loaded',
                'Missing basedn in bind options',
                'Missing binddn in bind options',
                'Missing bindpw in bind options',
                'Missing filter in bind options',
                'Invalid data passed. Must be a filename or array of users',
            ];

            $usernamePswdMismatchIssues = [
                'The LDAP DN search failed',
                \Vespula\Auth\Adapter\Sql::ERROR_PASSWORD_COL,
                \Vespula\Auth\Adapter\Sql::ERROR_USERNAME_COL,
                'Invalid credentials array. Must have keys `username` and `password`.',
            ];
            /** @psalm-suppress MixedAssignment */
            $_msg = $this->getAppSetting('base_controller_do_login_auth_v_auth_exception_general_msg');

            if(\in_array($vaExc->getMessage(), $backendIssues) || str_starts_with($vaExc->getMessage(), 'File not found ')) {
                /** @psalm-suppress MixedAssignment */
                $_msg = $this->getAppSetting('base_controller_do_login_auth_v_auth_exception_back_end_msg');
            }

            if(\in_array($vaExc->getMessage(), $usernamePswdMismatchIssues)) {
                /** @psalm-suppress MixedAssignment */
                $_msg = $this->getAppSetting('base_controller_do_login_auth_v_auth_exception_user_passwd_msg');
            }

            if(
                $this->getContainer()->has('logger')
                && ( $this->getContainer()->get('logger') instanceof \Psr\Log\LoggerInterface )
            ){
                /** 
                 * @psalm-suppress MixedArgument
                 * @psalm-suppress MixedMethodCall
                 * @psalm-suppress PossiblyInvalidOperand 
                 */
                $this->getContainer()
                     ->get('logger')
                     ->error( 
                        \str_replace('<br>', PHP_EOL, $_msg)
                        . Utils::getThrowableAsStr($vaExc)
                     );
            }

        } catch(\Exception $basExc) {
            
            $_msg = $this->getAppSetting('base_controller_do_login_auth_exception_msg');

            if(
                $this->getContainer()->has('logger')
                && ( $this->getContainer()->get('logger') instanceof \Psr\Log\LoggerInterface )
            ) {
                /** 
                 * @psalm-suppress MixedArgument
                 * @psalm-suppress MixedMethodCall
                 * @psalm-suppress PossiblyInvalidOperand 
                 */
                $this->getContainer()
                     ->get('logger')
                     ->error(
                        \str_replace('<br>', PHP_EOL, $_msg)
                        . Utils::getThrowableAsStr($basExc)
                     );
            }
        }
        /** @psalm-suppress MixedReturnStatement */
        return $_msg;
    }

    /**
     * @param mixed $show_status_on_completion any value that evaluates to true or false.
     *                                         When the value is true, the user will be
     *                                         redirected to actionLoginStatus(). When it
     *                                         is false, the user will be redirected to
     *                                         actionLogin()
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function actionLogout($show_status_on_completion = false): ResponseInterface {
        
        $auth = $this->vespula_auth;
        $auth->logout(); //logout
        
        if( !$auth->isAnon() ) {

            //logout failed. Definitely redirect to actionLoginStatus
            $show_status_on_completion = true;
        }

        // SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES === true
        // means that links generated in this action do not need to be prefixed
        // with action- since when users click on them, the framework will 
        // automatically append action to the resolved method name
        // see \SlimMvcTools\MvcRouteHandler::__invoke(...)
        /** @psalm-suppress UndefinedConstant */
        $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;
        $action = ($prepend_action) ? 'action-' : '';
        $actn = ($show_status_on_completion) ? $action.'login-status' : $action.'login';

        $controller = $this->controller_name_from_uri;

        if( ($controller === '') ) {

            $controller = 'base-controller';
        }

        $redirect_path = $this->makeLink("/{$controller}/{$actn}");

        /** @psalm-suppress MixedArrayOffset */
        if(
            session_status() === PHP_SESSION_ACTIVE
            && isset($_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT])
        ) {
            //there is an active session with a redirect url stored in it
            /** @psalm-suppress MixedAssignment */
            $redirect_path = $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT];
        }

        //re-direct
        /** @psalm-suppress MixedArgument */
        return $this->response->withStatus(302)->withHeader('Location', $redirect_path);
    }
    
    /** 
     * @psalm-suppress PossiblyUnusedMethod 
     * @psalm-suppress UnusedVariable
     */
    public function actionLoginStatus(): string {
        
        $msg = '';
        $auth = $this->vespula_auth;

        //Just get the current login status
        switch (true) {

            case $auth->isAnon():
                /** @psalm-suppress MixedAssignment */
                $msg = $this->getAppSetting('base_controller_action_login_status_is_anon_msg');
                break;

            case $auth->isIdle():
                /** @psalm-suppress MixedAssignment */
                $msg = $this->getAppSetting('base_controller_action_login_status_is_idle_msg');
                break;

            case $auth->isExpired():
                /** @psalm-suppress MixedAssignment */
                $msg = $this->getAppSetting('base_controller_action_login_status_is_expired_msg');
                break;

            case $auth->isValid():
                /** @psalm-suppress MixedAssignment */
                $msg = $this->getAppSetting('base_controller_action_login_status_is_valid_msg');
                break;

            default:
                /** @psalm-suppress MixedAssignment */
                $msg =  $this->getAppSetting('base_controller_action_login_status_unknown_msg');
                break;
        }

        /** 
         * @psalm-suppress UndefinedConstant
         * @psalm-suppress UndefinedFunction
         */
        if( sMVC_GetCurrentAppEnvironment() !== SMVC_APP_ENV_PRODUCTION ) {
            /** @psalm-suppress MixedOperand */
            $msg .= '<br>'.nl2br(sMVC_DumpAuthinfo($auth));
        }

        //get the contents of the view first
        $view_str = $this->renderView( 'login-status.php', ['message'=>$msg, 'is_logged_in'=>$this->isLoggedIn(), 'controller_object'=>$this] );

        return $this->renderLayout( $this->layout_template_file_name, ['content'=>$view_str] );
    }

    public function isLoggedIn(): bool {
        
        return ($this->vespula_auth->isValid());
    }

    /**
     * Return a response object (an instance of \Psr\Http\Message\ResponseInterface)
     * if the user is not logged in (The url the user is currently accessing will be
     * stored in $_SESSION with the key `static::SESSN_PARAM_LOGIN_REDIRECT`. Upon
     * a successful login, the user will be redirected back to the current url in
     * $this->actionLogin().
     *
     * False is returned if the user is logged in and there is no need to redirect to
     * the login page.
     *
     * @return bool|\Psr\Http\Message\ResponseInterface
     */
    public function getResponseObjForLoginRedirectionIfNotLoggedIn() {

        if( !$this->isLoggedIn() ) {

            $this->storeCurrentUrlForLoginRedirection();

            $controller = $this->controller_name_from_uri;

            if( ($controller === '') ) {

                $controller = 'base-controller';
            }

            // SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES === true
            // means that links generated in this action do not need to be prefixed
            // with action- since when users click on them, the framework will 
            // automatically append action to the resolved method name
            // see \SlimMvcTools\MvcRouteHandler::__invoke(...)
            /** @psalm-suppress UndefinedConstant */
            $action = (SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES) ? 'login' : 'action-login';
            $redr_path = $this->makeLink("/{$controller}/$action");

            return $this->response->withStatus(302)->withHeader('Location', $redr_path);
        }

        return false;
    }

    public function preAction(): ResponseInterface {

        return $this->response;
    }

    public function postAction(\Psr\Http\Message\ResponseInterface $response): ResponseInterface {

        return $response;
    }

    public function storeCurrentUrlForLoginRedirection(): self {

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
        ) { return $this; }

        // Use the uri to grab the query string & the fragment part 
        // (i.e. the part that starts with #)
        // we will append them to the app-base-path + controller + action
        $uri = $this->request->getUri();
        $fragment = $uri->getFragment();
        $query = $uri->getQuery();
        
        $path = $this->controller_name_from_uri;
        
        if($path !== '') {
            
            // There's no way we can have the action part without a preceeding 
            // controller part in any uri path.
            $path .= ($this->action_name_from_uri === '') ? '' : '/' .$this->action_name_from_uri;
        }
        
        $curr_url = $this->makeLink($path)
                    . ( ($query !== '') ? '?' . $query : '' )
                    . ( ($fragment !== '') ? '#' . $fragment : '' );

        //start a new session if none exists
        if(session_status() !== PHP_SESSION_ACTIVE) {
            
            session_start();
        }

        //store current url in session
        /** @psalm-suppress MixedArrayOffset */
        $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT] = $curr_url;
        
        return $this;
    }

    /**
     * @return mixed
     *
     * @throws \Slim\Exception\HttpInternalServerErrorException
     */
    public function getContainerItem(string $item_key_in_container) {

        if( $this->getContainer()->has($item_key_in_container) ) {

            return $this->getContainer()->get($item_key_in_container);

        } else {

            $msg = "ERROR: The item with the key named `$item_key_in_container` does not exist in"
                 . " the container associated with `" . get_class($this) . "` ."
                 . PHP_EOL;

            throw new \Slim\Exception\HttpInternalServerErrorException($this->request, $msg);
        }
    }

    public function getContainer(): \Psr\Container\ContainerInterface {

        return $this->container;
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp400(string $message, ?ServerRequestInterface $request=null): void {
        
        throw new \Slim\Exception\HttpBadRequestException(($request ?? $this->request), $message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp401(string $message, ?ServerRequestInterface $request=null): void {
        
        throw new \Slim\Exception\HttpUnauthorizedException(($request ?? $this->request), $message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp403(string $message, ?ServerRequestInterface $request=null): void {
        
        throw new \Slim\Exception\HttpForbiddenException(($request ?? $this->request), $message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp404(string $message, ?ServerRequestInterface $request=null): void {
        
        throw new \Slim\Exception\HttpNotFoundException(($request ?? $this->request), $message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp405(string $message, ?ServerRequestInterface $request=null): void {
        
        throw new \Slim\Exception\HttpMethodNotAllowedException(($request ?? $this->request), $message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp410(string $message, ?ServerRequestInterface $request=null): void {
        
        throw new \Slim\Exception\HttpGoneException(($request ?? $this->request), $message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp500(string $message, ?ServerRequestInterface $request=null): void {
        
        throw new \Slim\Exception\HttpInternalServerErrorException(($request ?? $this->request), $message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp501(string $message, ?ServerRequestInterface $request=null): void {
        
        throw new \Slim\Exception\HttpNotImplementedException(($request ?? $this->request), $message);
    }
}
