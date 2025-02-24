<?php
declare(strict_types=1);

namespace SlimMvcTools\Controllers;

use \Psr\Http\Message\ServerRequestInterface,
    \Psr\Http\Message\ResponseInterface,
    \SlimMvcTools\ContainerKeys,
    \SlimMvcTools\Utils;

/**
 * A base controller class that should be extended to build mvc controllers
 * in https://github.com/rotexsoft/slim-skeleton-mvc-app applications. 
 * There is a command-line tool for building such controllers.
 *
 * @author Rotimi Adegbamigbe
 */
class BaseController
{
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
     *      - doLogin
     *  - actionLogout
     *  - actionLoginStatus
     */
    protected \Vespula\Auth\Auth $vespula_auth;
    
    /**
     * Object for getting locale specific translations of text to be displayed
     */
    protected \Vespula\Locale\Locale $vespula_locale;
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getVespulaLocale(): \Vespula\Locale\Locale {
        
        return $this->vespula_locale;
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function setVespulaLocale(\Vespula\Locale\Locale $nu_locale): self {
        
        $this->vespula_locale = $nu_locale;
        
        return $this;
    }
    
    /**
     * Object for logging events
     */
    protected \Psr\Log\LoggerInterface $logger;
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getLogger(): \Psr\Log\LoggerInterface {
        
        return $this->logger;
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function setLogger(\Psr\Log\LoggerInterface $nu_logger): self {
        
        $this->logger = $nu_logger;
        
        return $this;
    }
    

    /**
     * Will be used in actionLogin() to construct the url to redirect to upon successful login,
     * if $_SESSION[self::SESSN_PARAM_LOGIN_REDIRECT] is not set.
     */
    protected string $login_success_redirect_action = 'index';
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getLoginSuccessRedirectAction(): string {
        
        return $this->login_success_redirect_action;
    }
    
    /**
     * Will be used in actionLogin() to construct the url to redirect to upon successful login,
     * if $_SESSION[self::SESSN_PARAM_LOGIN_REDIRECT] is not set.
     */
    protected string $login_success_redirect_controller = 'base-controller';
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getLoginSuccessRedirectController(): string {
        
        return $this->login_success_redirect_controller;
    }

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
     * The name of the layout file that will be rendered by $this->layout_renderer inside
     * $this->renderLayout(..)
     */
    protected string $layout_template_file_name = 'main-template.php';
    
    //////////////////////////////////
    // Constants
    //////////////////////////////////
    public const GET_QUERY_PARAM_SELECTED_LANG = 'selected_lang';
    
    //////////////////////////////////
    // Session Parameter keys
    //////////////////////////////////
    public const SESSN_PARAM_LOGIN_REDIRECT = self::class . '_login_redirect_path';
    
    // This item in the session represents the current language selected by the user
    public const SESSN_PARAM_CURRENT_LOCALE_LANG = self::class . '_current_locale_language';
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(
        /**
         * A container object containing dependencies needed by the controller.
         */
        protected \Psr\Container\ContainerInterface $container, 
        string $controller_name_from_uri,
        string $action_name_from_uri,
        /**
         * Request Object
         */
        protected \Psr\Http\Message\ServerRequestInterface $request, 
        /**
         * Response Object
         */
        protected \Psr\Http\Message\ResponseInterface $response
    ) {
        $this->action_name_from_uri = ($action_name_from_uri !== '') ? $action_name_from_uri : $this->action_name_from_uri;
        $this->controller_name_from_uri = ($controller_name_from_uri !== '') ? $controller_name_from_uri : $this->controller_name_from_uri;
        
        /** @psalm-suppress MixedAssignment */
        $this->logger = $this->getContainerItem(ContainerKeys::LOGGER);
        
        /** @psalm-suppress MixedAssignment */
        $this->vespula_locale = $this->getContainerItem(ContainerKeys::LOCALE_OBJ);
        
        /** @psalm-suppress MixedAssignment */
        $this->vespula_auth = $this->getContainerItem(ContainerKeys::VESPULA_AUTH);
        
        /**
         * @psalm-suppress MixedAssignment
         */
        $this->layout_renderer = $this->getContainerItem(ContainerKeys::LAYOUT_RENDERER);
        
        /**
         * @psalm-suppress MixedAssignment
         */
        $this->view_renderer = $this->getContainerItem(ContainerKeys::VIEW_RENDERER);
        
        $uri_path = ($this->request->getUri() instanceof \Psr\Http\Message\UriInterface)
                                                ? $this->request->getUri()->getPath() : '';
        
        if( 
            ( ($this->controller_name_from_uri === '') || ($this->action_name_from_uri === '') )
            && ( ($uri_path !== '') && ($uri_path !== '/') && (str_contains($uri_path, '/')) )
        ) {
            // Calculate $this->controller_name_from_uri and / or
            // $this->action_name_from_uri if necessary
            if( $uri_path[0] === '/' ) {

                // Remove leading slash /
                $uri_path = substr($uri_path, 1);
            }

            $uri_path_parts = explode('/', $uri_path);

            if( ($this->controller_name_from_uri === '') ) {

                $this->controller_name_from_uri = $uri_path_parts[0];
            }

            if( count($uri_path_parts) >= 2 && ($this->action_name_from_uri === '') ) {

                $this->action_name_from_uri = $uri_path_parts[1];
            }
        }
        
        $this->updateSelectedLanguage();
    }

    protected function startSession(): void {
        
        $session_start_settings = 
            $this->getAppSetting('session_start_options') !== null
                ? (array)$this->getAppSetting('session_start_options')
                : [];
        
        if(isset($session_start_settings['name'])) {
            
            session_name((string)$session_start_settings['name']);
        }

        session_start($session_start_settings);
    }
    
    /**
     * @psalm-suppress InvalidScalarArgument
     */
    public function updateSelectedLanguage() : void {

        $query_params = $this->request->getQueryParams();

        /**
         * @psalm-suppress MixedArgument
         */
        if( 
            array_key_exists(self::GET_QUERY_PARAM_SELECTED_LANG, $query_params)
            && in_array(
                $query_params[self::GET_QUERY_PARAM_SELECTED_LANG], 
                $this->getContainerItem(ContainerKeys::VALID_LOCALES)
            )
        ) {
            // User specified a language in the uri which is an acceptable
            // language defined for this application
            /**
             * @psalm-suppress MixedArgument
             */
            $this->vespula_locale->setCode($query_params[self::GET_QUERY_PARAM_SELECTED_LANG]);

            if (session_status() !== \PHP_SESSION_ACTIVE) {

                $this->startSession();
            }

            // also store in session
            /**
             * @psalm-suppress MixedAssignment
             */
            $_SESSION[self::SESSN_PARAM_CURRENT_LOCALE_LANG] = 
                $query_params[self::GET_QUERY_PARAM_SELECTED_LANG];
        } elseif (
            session_status() === \PHP_SESSION_ACTIVE         
            && array_key_exists(self::SESSN_PARAM_CURRENT_LOCALE_LANG, $_SESSION)
        ) {
            $this->vespula_locale->setCode($_SESSION[self::SESSN_PARAM_CURRENT_LOCALE_LANG]);
        }
        // else { // default lang is already preconfigured in dependencies file }
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
        $settings = $this->getContainerItem(ContainerKeys::APP_SETTINGS);
        
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
     */
    public function renderLayout( string $file_name, array $data = ['content'=>'Content should be placed here!'] ): string {

        $self = $this;
        $data['sMVC_MakeLink'] = fn(string $path): string => $self->makeLink($path);
        $data['controller_object'] = $this;
        
        /**
         * @psalm-suppress MixedAssignment
         */
        $this->layout_renderer = $this->getContainerItem(ContainerKeys::LAYOUT_RENDERER); // get new instance for each call to this method renderLayout
        
        /** 
         * @psalm-suppress MixedReturnStatement
         * @psalm-suppress MixedMethodCall
         */
        return $this->layout_renderer->renderToString($file_name, $data);
    }

    /**
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
        
        /**
         * @psalm-suppress MixedAssignment
         */
        $this->view_renderer = $this->getContainerItem(ContainerKeys::VIEW_RENDERER);  // get new instance for each call to this method renderView

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
        $data['controller_object'] = $this;
        
        /** 
         * @psalm-suppress MixedReturnStatement
         * @psalm-suppress MixedMethodCall
         */
        return $this->view_renderer->renderToString($file_name, $data);
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function actionIndex(): ResponseInterface|string {

        //get the contents of the view first
        $view_str = $this->renderView('index.php', ['controller_object'=>$this]);

        return $this->renderLayout( $this->layout_template_file_name, ['content'=>$view_str] );
    }

    /**
     * Display an HTML table containing all the potential MVC routes in the application
     *
     * @param bool $onlyPublicMethodsPrefixedWithAction true to include only public methods prefixed with `action`
     *                                                  or false to include all public methods
     * @param bool $stripActionPrefixFromMethodName true to strip the `action-` prefix from methods displayed or false to leave the `action-` prefix
     * 
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function actionRoutes($onlyPublicMethodsPrefixedWithAction=true, $stripActionPrefixFromMethodName=true): ResponseInterface|string {

        $resp = $this->getResponseObjForLoginRedirectionIfNotLoggedIn();

        if($resp instanceof \Psr\Http\Message\ResponseInterface) {

            return $resp;
        }

        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '0');

        /** @psalm-suppress RedundantCastGivenDocblockType */
        $view_str = $this->renderView(
            'controller-classes-by-action-methods-report.php',
            [
                'onlyPublicMethodsPrefixedWithAction'=> ((bool)$onlyPublicMethodsPrefixedWithAction),
                'stripActionPrefixFromMethodName'=> ((bool)$stripActionPrefixFromMethodName),
            ]
        );

        return $this->renderLayout( $this->layout_template_file_name, ['content'=>$view_str] );
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function actionLogin(): ResponseInterface|string {

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
            $username = sMVC_GetSuperGlobal('post', 'username', '');

            /** @psalm-suppress MixedAssignment */
            $password = sMVC_GetSuperGlobal('post', 'password', '');
            $error_msg = '';

            if( $username === '' ) {
                /** 
                 * @psalm-suppress MixedOperand
                 */
                $error_msg .= $this->vespula_locale
                                   ->gettext('base_controller_action_login_empty_username_msg');
            }

            if( $password === '' ) {

                $error_msg .= (($error_msg === ''))? '' : '<br>';
                /** 
                 * @psalm-suppress MixedOperand
                 */
                $error_msg .= $this->vespula_locale->gettext('base_controller_action_login_empty_password_msg');
            }

            if( ($error_msg === '') ) {

                $credentials = [
                    'username'=> filter_var($username, FILTER_UNSAFE_RAW),
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
            if( sMVC_GetCurrentAppEnvironment() !== SMVC_APP_ENV_PRODUCTION ) {

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
            
            $potential_success_redirect_path = '';
            
            /** @psalm-suppress MixedArrayOffset */
            if( isset($_SESSION[self::SESSN_PARAM_LOGIN_REDIRECT]) ) {

                ////////////////////////////////////////////////////////////////
                // There is an active session with a redirect url stored in it
                //
                // NOTE: we capture this value here because \Vespula\Auth\Auth->login()
                // calls session_regenerate_id(true) under the hood which will delete
                // old session data including this value we are capturing here.
                /** @psalm-suppress MixedAssignment */
                $potential_success_redirect_path = $_SESSION[self::SESSN_PARAM_LOGIN_REDIRECT];
            }
            
            $auth->login($credentials); //try to login

            if( $auth->isValid() ) { // login successful

                /** 
                 * @psalm-suppress MixedArgument
                 * @psalm-suppress MixedMethodCall
                 * @psalm-suppress PossiblyInvalidOperand 
                 */
                $this->logger
                     ->info( 
                        "User `{$auth->getUsername()}` successfully logged in." . PHP_EOL .PHP_EOL
                     );
                
                /**
                 * @psalm-suppress MixedAssignment 
                 * @psalm-suppress MixedOperand
                 */
                $_msg = $this->vespula_locale->gettext('base_controller_do_login_auth_is_valid_msg');
                
                /** @psalm-suppress MixedAssignment */
                $success_redirect_path = 
                    ($potential_success_redirect_path !== '') 
                        ? $potential_success_redirect_path : $success_redirect_path;
                
                /** @psalm-suppress MixedArrayOffset */
                if( isset($_SESSION[self::SESSN_PARAM_LOGIN_REDIRECT]) ) {

                    //since login is successful remove stored redirect url, 
                    //it has served its purpose & we'll be redirecting now.
                    unset($_SESSION[self::SESSN_PARAM_LOGIN_REDIRECT]);
                }

                //since we are successfully logged in, resume session if any
                if (session_status() !== \PHP_SESSION_ACTIVE) { 
                    
                    $this->startSession();
                }

            } else {
                /**
                 * @psalm-suppress MixedAssignment 
                 * @psalm-suppress MixedOperand
                 */
                $_msg = $this->vespula_locale->gettext('base_controller_do_login_auth_not_is_valid_msg');

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
            
            /**
             * @psalm-suppress MixedAssignment 
             * @psalm-suppress MixedOperand
             */
            $_msg = $this->vespula_locale->gettext('base_controller_do_login_auth_v_auth_exception_general_msg');

            if(\in_array($vaExc->getMessage(), $backendIssues) || str_starts_with($vaExc->getMessage(), 'File not found ')) {
                /**
                 * @psalm-suppress MixedAssignment 
                 * @psalm-suppress MixedOperand
                 */
                $_msg = $this->vespula_locale->gettext('base_controller_do_login_auth_v_auth_exception_back_end_msg');
            }

            if(\in_array($vaExc->getMessage(), $usernamePswdMismatchIssues)) {
                /**
                 * @psalm-suppress MixedAssignment 
                 * @psalm-suppress MixedOperand
                 */
                $_msg = $this->vespula_locale->gettext('base_controller_do_login_auth_v_auth_exception_user_passwd_msg');
            }
            
            /** 
             * @psalm-suppress MixedArgument
             * @psalm-suppress MixedMethodCall
             * @psalm-suppress PossiblyInvalidOperand
             */
            $this->logger
                 ->error( 
                    \str_replace('<br>', PHP_EOL, $_msg)
                    . Utils::getThrowableAsStr($vaExc)
                 );

        } catch(\Exception $basExc) {
            /**
             * @psalm-suppress MixedAssignment 
             * @psalm-suppress MixedOperand
             */
            $_msg = $this->vespula_locale->gettext('base_controller_do_login_auth_exception_msg');

            /** 
             * @psalm-suppress MixedArgument
             * @psalm-suppress MixedMethodCall
             * @psalm-suppress PossiblyInvalidOperand
             */
            $this->logger
                 ->error(
                    \str_replace('<br>', PHP_EOL, $_msg)
                    . Utils::getThrowableAsStr($basExc)
                 );
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
    public function actionLogout(mixed $show_status_on_completion = false): ResponseInterface {
        
        $auth = $this->vespula_auth;
        $logged_in_user = $this->isLoggedIn() ? $auth->getUsername() : '';
        $redirect_path = '';

        /** @psalm-suppress MixedArrayOffset */
        if(
            session_status() === \PHP_SESSION_ACTIVE
            && isset($_SESSION[self::SESSN_PARAM_LOGIN_REDIRECT])
        ) {
            //there is an active session with a redirect url stored in it
            /** @psalm-suppress MixedAssignment */
            $redirect_path = $_SESSION[self::SESSN_PARAM_LOGIN_REDIRECT];
        }
        
        $auth->logout(); //logout
        
        if( !$auth->isAnon() ) {

            //logout failed. Definitely redirect to actionLoginStatus
            $show_status_on_completion = true;
            
        } elseif ($logged_in_user !== '') {
            
            /** 
             * @psalm-suppress MixedArgument
             * @psalm-suppress MixedMethodCall
             * @psalm-suppress PossiblyInvalidOperand 
             */
            $this->logger
                 ->info( 
                    "User `{$logged_in_user}` successfully logged out" . PHP_EOL .PHP_EOL
                 );
        }

        if($redirect_path === '' || ((bool)$show_status_on_completion)) {
            
            // SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES === true
            // means that links generated in this action do not need to be prefixed
            // with action- since when users click on them, the framework will 
            // automatically append action to the resolved method name
            // see \SlimMvcTools\MvcRouteHandler::__invoke(...)
            /** @psalm-suppress UndefinedConstant */
            $prepend_action = !SMVC_APP_AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES;
            $action = ($prepend_action) ? 'action-' : '';
            $actn = ((bool)$show_status_on_completion) ? $action.'login-status' : $action.'login';

            $controller = $this->controller_name_from_uri;

            if( ($controller === '') ) {

                $controller = 'base-controller';
            }

            $redirect_path = $this->makeLink("/{$controller}/{$actn}");
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
        /** @psalm-suppress MixedAssignment */
        $msg = match (true) {
            $auth->isAnon() => $this->vespula_locale->gettext('base_controller_action_login_status_is_anon_msg'),
            $auth->isIdle() => $this->vespula_locale->gettext('base_controller_action_login_status_is_idle_msg'),
            $auth->isExpired() => $this->vespula_locale->gettext('base_controller_action_login_status_is_expired_msg'),
            $auth->isValid() => $this->vespula_locale->gettext('base_controller_action_login_status_is_valid_msg'),
            default => $this->vespula_locale->gettext('base_controller_action_login_status_unknown_msg'),
        };

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
     */
    public function getResponseObjForLoginRedirectionIfNotLoggedIn(): bool|\Psr\Http\Message\ResponseInterface {

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

        /** @psalm-suppress RedundantCast */
        if(
            in_array(
                strtolower($this->action_name_from_uri),
                [
                    'login', 'action-login', 'actionlogin', 'action_login',
                    'logout', 'action-logout', 'actionlogout', 'action_logout'
                ]
            )
        ) { return $this; }

        // Use the uri to grab the query string & the fragment part 
        // (i.e. the part that starts with #)
        // we will append them to the app-base-path + controller + action
        $uri = $this->request->getUri();
        $fragment = $uri->getFragment();
        $query = $uri->getQuery();
        $path = $uri->getPath();
        $curr_url = $path
                    . ( ($query !== '') ? '?' . $query : '' )
                    . ( ($fragment !== '') ? '#' . $fragment : '' );

        //start a new session if none exists
        if(session_status() !== \PHP_SESSION_ACTIVE) {
            
            $this->startSession();
        }

        //store current url in session
        /** @psalm-suppress MixedArrayOffset */
        $_SESSION[self::SESSN_PARAM_LOGIN_REDIRECT] = $curr_url;
        
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
                 . " the container associated with `" . static::class . "` ."
                 . PHP_EOL;
            
            throw Utils::createSlimHttpExceptionWithLocalizedDescription(
                $this->getContainer(),
                \SlimMvcTools\SlimHttpExceptionClassNames::HttpInternalServerErrorException,
                $this->request,
                $msg
            );
        }
    }

    public function getContainer(): \Psr\Container\ContainerInterface {

        return $this->container;
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp400(string $message, ?ServerRequestInterface $request=null): void {
        
        throw (new \Slim\Exception\HttpBadRequestException(($request ?? $this->request), $message))->setDescription($message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp401(string $message, ?ServerRequestInterface $request=null): void {
        
        throw (new \Slim\Exception\HttpUnauthorizedException(($request ?? $this->request), $message))->setDescription($message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp403(string $message, ?ServerRequestInterface $request=null): void {
        
        throw (new \Slim\Exception\HttpForbiddenException(($request ?? $this->request), $message))->setDescription($message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp404(string $message, ?ServerRequestInterface $request=null): void {
        
        throw (new \Slim\Exception\HttpNotFoundException(($request ?? $this->request), $message))->setDescription($message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp405(string $message, ?ServerRequestInterface $request=null): void {
        
        throw (new \Slim\Exception\HttpMethodNotAllowedException(($request ?? $this->request), $message))->setDescription($message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp410(string $message, ?ServerRequestInterface $request=null): void {
        
        throw (new \Slim\Exception\HttpGoneException(($request ?? $this->request), $message))->setDescription($message);
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp429(string $message, ?ServerRequestInterface $request=null): void {
        
        throw (new \Slim\Exception\HttpTooManyRequestsException(($request ?? $this->request), $message))->setDescription($message);
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp500(string $message, ?ServerRequestInterface $request=null): void {
        
        throw (new \Slim\Exception\HttpInternalServerErrorException(($request ?? $this->request), $message))->setDescription($message);
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function forceHttp501(string $message, ?ServerRequestInterface $request=null): void {
        
        throw (new \Slim\Exception\HttpNotImplementedException(($request ?? $this->request), $message))->setDescription($message);
    }
}
