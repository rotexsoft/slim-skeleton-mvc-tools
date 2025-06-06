<?php
declare(strict_types=1);

use \SlimMvcTools\{ContainerKeys, Utils, SlimHttpExceptionClassNames};

/**
 * Creates & returns a controller object that is an instance of 
 * \SlimMvcTools\Controllers\BaseController or its sub-classes
 *  
 * The controller class must be \SlimMvcTools\Controllers\BaseController 
 * or one of its sub-classes
 * 
 * @throws \Slim\Exception\HttpBadRequestException
 * @throws \Slim\Exception\HttpNotFoundException
 */
function sMVC_CreateController(
    \Psr\Container\ContainerInterface $container, 
    string $controller_name_from_url, 
    string $action_name_from_url,
    \Psr\Http\Message\ServerRequestInterface $request, 
    \Psr\Http\Message\ResponseInterface $response
):\SlimMvcTools\Controllers\BaseController {
    
    $controller_class_name = \SlimMvcTools\Functions\Str\dashesToStudly($controller_name_from_url);
    $regex_4_valid_class_name = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    if( 
        !preg_match( $regex_4_valid_class_name, preg_quote($controller_class_name, '/') )
    ) {
        //A valid php class name starts with a letter or underscore, followed by 
        //any number of letters, numbers, or underscores.
        /** @psalm-suppress InvalidOperand */
        $extra_log_message = "`" . __FILE__ . "` on line " . __LINE__ . ": Bad controller name `{$controller_class_name}`";
        
        throw Utils::createSlimHttpExceptionWithLocalizedDescription(
            $container,
            SlimHttpExceptionClassNames::HttpBadRequestException,
            $request,
            $extra_log_message
        );
    } // if( !preg_match( $regex_4_valid_class_name, preg_quote($controller_class_name, '/') ) )

    if( !class_exists($controller_class_name) ) {
        
        if( $container->has(ContainerKeys::NAMESPACES_4_CONTROLLERS) ) {
            
            /** @psalm-suppress MixedAssignment */
            $namespaces_4_controllers = $container->get(ContainerKeys::NAMESPACES_4_CONTROLLERS);

            //try to prepend name space
            /** @psalm-suppress MixedAssignment */
            foreach($namespaces_4_controllers as $namespace_4_controllers) {

                /** @psalm-suppress MixedOperand */
                if( class_exists($namespace_4_controllers.$controller_class_name) ) {

                    /** @psalm-suppress MixedOperand */
                    $controller_class_name = $namespace_4_controllers.$controller_class_name;
                    break;
                    
                } // if( class_exists($namespace_4_controllers.$controller_class_name) )
            } // foreach($namespaces_4_controllers as $namespace_4_controllers)
        } // if( $container->has(ContainerKeys::NAMESPACES_4_CONTROLLERS) )
        
        //class still doesn't exist
        if( !class_exists($controller_class_name) ) {

            //404 Not Found: Controller class not found.
            /** @psalm-suppress InvalidOperand */
            $extra_log_message = "`".__FILE__."` on line ".__LINE__.": Class `{$controller_class_name}` does not exist.";
            throw Utils::createSlimHttpExceptionWithLocalizedDescription(
                $container,
                SlimHttpExceptionClassNames::HttpNotFoundException,
                $request,
                $extra_log_message
            );
            
        } // if( !class_exists($controller_class_name) )
    } // if( !class_exists($controller_class_name) )
    
    if( !is_a($controller_class_name, \SlimMvcTools\Controllers\BaseController::class, true) ) {

        //400 Bad Request: Controller class is not a subclass of \SlimMvcTools\Controllers\BaseController.
        /** @psalm-suppress InvalidOperand */
        $extra_log_message = 
                "`".__FILE__."` on line ".__LINE__
                . sprintf(': `%s` could not be mapped to a valid controller.', $request->getUri()->__toString());
        
        throw Utils::createSlimHttpExceptionWithLocalizedDescription(
            $container,
            SlimHttpExceptionClassNames::HttpBadRequestException,
            $request,
            $extra_log_message
        );
        
    } // if( !is_a($controller_class_name, \SlimMvcTools\Controllers\BaseController::class, true) )

    //Create the controller object
    /** @psalm-suppress UnsafeInstantiation */
    return new $controller_class_name($container, $controller_name_from_url, $action_name_from_url, $request, $response);
}

/**
 * @return string containing current authentication status info
 */
function sMVC_DumpAuthinfo(\Vespula\Auth\Auth $auth): string {

    return 'Login Status: ' . ( $auth->getSession()->getStatus() ?? '' ) . PHP_EOL
         . 'Logged in Person\'s Username: ' . ( $auth->getUsername() ?? '' ).PHP_EOL
         . 'Logged in User\'s Data: ' . PHP_EOL . print_r($auth->getUserdata(), true);
}

/**
 * @param mixed[] $vals variables or expressions to dump
 */
function sMVC_DumpVar(...$vals): void {

    /** @psalm-suppress MissingClosureParamType */
    $var_to_string = function($var): string {

        // Start capturing the output
        ob_start();

        /** @psalm-suppress ForbiddenCode */
        var_dump($var);

        // Get the captured output, close the buffer & return the captured output
        $output = ob_get_clean();

        return ($output === false) ? '' : $output;
    };

    $line_breaker = (PHP_SAPI === 'cli') ? PHP_EOL : '<br>';
    $pre_open = (PHP_SAPI === 'cli') ? '' : '<pre>';
    $pre_close = (PHP_SAPI === 'cli') ? '' : '</pre>';

    /** @psalm-suppress MixedAssignment */
    foreach($vals as $val) {

        echo $pre_open . $var_to_string($val) . $pre_close . $line_breaker;
    }
}

/**
 * This function stores a snapshot of the following super globals $_SERVER, $_GET,
 * $_POST, $_FILES, $_COOKIE, $_SESSION & $_ENV and then returns the stored values
 * on subsequent calls. (In the case of $_SESSION, a reference to it is kept so
 * that modifying sMVC_GetSuperGlobal('session') will also modify $_SESSION).
 * If a session has not been started sMVC_GetSuperGlobal('session') will always
 * return null, likewise sMVC_GetSuperGlobal('session', 'some_key') will always
 * return $default_val.
 * 
 * IT IS STRONGLY RECOMMENDED THAY YOU USE LIBRARIES LIKE aura/session
 * (https://github.com/auraphp/Aura.Session) TO WORK WITH $_SESSION.
 * USING sMVC_GetSuperGlobal('session') IS HIGHLY DISCOURAGED.
 * 
 * @param string $global_name the name (case-insensitive) of a any of the super
 *                            globals mentioned above (excluding the $_). For
 *                            example 'Post', 'pOst', etc.
 *                            sMVC_GetSuperGlobal('get') === sMVC_GetSuperGlobal('gEt'), etc.
 * 
 * @param string $key a key in the specified super global. For example $_GET['id']
 *                    is equivalent to sMVC_GetSuperGlobal('get', 'id');
 * 
 * @param mixed $default_val the value to return if $key is not an actual key in
 *                            the specified super global.
 * 
 * @return mixed Returns an array containing all values in the specified super
 *               global if $key and $default_val were not supplied. A value associated
 *               with a specific key in the specified super global is returned or the
 *               $default_val if the specific key is not found in the specified super
 *               global (this happens when $global_name and $key are supplied;
 *               $default_val may be supplied too). If no parameters were supplied
 *               an array with the following keys
 *              (`server`, `get`, `post`, `files`, `cookie`, `env` and `session`)
 *              is returned (the corresponding values will be the value of the
 *              super global associated with each key).
 * 
 */
function sMVC_GetSuperGlobal(string $global_name='', string $key='', mixed $default_val='') {

    static $super_globals;

    $is_session_started = (session_status() === PHP_SESSION_ACTIVE);

    if( !$super_globals ) {

        $super_globals = [];
        
        /** 
         * @psalm-suppress RedundantCondition 
         * @psalm-suppress TypeDoesNotContainNull 
         */
        $super_globals['server'] = $_SERVER ?? []; //copy
        
        /** 
         * @psalm-suppress RedundantCondition 
         * @psalm-suppress TypeDoesNotContainNull 
         */
        $super_globals['get'] = $_GET ?? []; //copy
        
        /** 
         * @psalm-suppress RedundantCondition 
         * @psalm-suppress TypeDoesNotContainNull 
         */
        $super_globals['post'] = $_POST ?? []; //copy
        
        /** 
         * @psalm-suppress RedundantCondition 
         * @psalm-suppress TypeDoesNotContainNull 
         */
        $super_globals['files'] = $_FILES ?? []; //copy
        
        /** 
         * @psalm-suppress RedundantCondition 
         * @psalm-suppress TypeDoesNotContainNull 
         */
        $super_globals['cookie'] = $_COOKIE ?? []; //copy
        
        /** 
         * @psalm-suppress RedundantCondition 
         * @psalm-suppress TypeDoesNotContainNull 
         */
        $super_globals['env'] = $_ENV ?? []; //copy

        if( $is_session_started ) {

            $super_globals['session'] =& $_SESSION; //obtain a reference

        } else {

            $super_globals['session'] = null;
        }
    }

    if( $global_name === '' ) {

        //return everything
        return $super_globals;
    }

    //normalize the global name
    $global_name = strtolower($global_name);

    if( str_starts_with($global_name, '$_') ) {

        $global_name = substr($global_name, 2);
    }

    if( $key === '' ) {

        //return everything for the specified global
        /** @psalm-suppress MixedArgument */
        return array_key_exists($global_name, $super_globals)
               ? $super_globals[$global_name] : [];
    }

    if( !$is_session_started && $global_name === 'session' ) {

        //return the default value because $super_globals['session'] === null
        return $default_val;
    }

    //return value of the specified key in the specified global or the default value
    /** 
     * @psalm-suppress MixedArrayAccess
     * @psalm-suppress MixedArgument
     * @psalm-suppress PossiblyNullArgument
     * @psalm-suppress PossiblyNullArrayAccess
     */
    return array_key_exists($key, $super_globals[$global_name])
           ? $super_globals[$global_name][$key] : $default_val;
}

/**
 * Converts a uri object to a string in the format <scheme>://<server_address>/<path>?<query_string>#<fragment>
 * 
 * @param \Psr\Http\Message\UriInterface $uri uri object to be converted to a string
 * 
 * @return string the string represntation of the uri object. 
 *                Eg. http://someserver.com/controller/action
 */
function sMVC_UriToString(\Psr\Http\Message\UriInterface $uri): string {
    
    return (string)$uri;
}

/**
 * Adds a query string parameter key/value pair to a uri object.
 * 
 * Given a uri object $uri1 representing http://someserver.com/controller/action?param1=val1
 * sMVC_addQueryStrParamToUri($uri1, 'param2', 'val2') will return a new uri object representing
 * http://someserver.com/controller/action?param1=val1&param2=val2
 */
function sMVC_AddQueryStrParamToUri(
    \Psr\Http\Message\UriInterface $uri, string $param_name, string $param_value
): \Psr\Http\Message\UriInterface {

    $query_params = [];
    parse_str($uri->getQuery(), $query_params); // Extract existing query string params to an array
    $query_params[$param_name] = $param_value; // Add new param the query string params array

    return $uri->withQuery(http_build_query($query_params)); // return a uri object with updated query params
}

/**
 * @param string $error_message A brief description of the message
 * @param string $file_path path to the missing file
 * @param string $dist_file_path path to the dist file that can be used to create the missing file
 * @param string $app_root_path should be set to the absolute path of where your mvc app is installed just pass SMVC_APP_ROOT_PATH
 */
function sMVC_DisplayAndLogFrameworkFileNotFoundError(
    string $error_message, 
    string $file_path, 
    string $dist_file_path, 
    string $app_root_path
): void {
    $file_missing_error_page = <<<END
<html>
    <head>
        <title>SlimPHP 4 Skeleton MVC App Error</title>
        <style>
            body{
                margin:0;
                padding:30px;
                font:14px/1.5 Helvetica,Arial,Verdana,sans-serif;
            }
            h1{
                margin:0;
                font-size:48px;
                font-weight:normal;
                line-height:48px;
            }
        </style>
    </head>
    <body>
        <h1>SlimPHP 4 Skeleton MVC App Error</h1>
        <p>
            {$error_message} <br>
            Please check the most recent server log file in (<strong>./logs</strong>) for details.
            <br>Goodbye!!!
        </p>
    </body>
</html>
END;
    echo $file_missing_error_page;

    $current_uri = $_SERVER['PATH_INFO'] ?? '';
    $current_uri .= isset($_SERVER['QUERY_STRING'])
                    ? '?' . $_SERVER['QUERY_STRING']
                    : '';

    // Write full message to log via error_log(...)
    // http://php.net/manual/en/function.error-log.php
    $log_message = "ERROR: [{$current_uri}] `{$file_path}` not found."
        . " Please copy `{$dist_file_path}` to `{$file_path}` and"
        . " configure `{$file_path}` for your application's current environment.";

    $ds = DIRECTORY_SEPARATOR;
    $file = $app_root_path . "{$ds}logs{$ds}daily_log_" . date('Y_M_d') . '.txt';

    file_put_contents(
        $file,
        '[' . date('Y-M-d g:i:s A') . '] ' . $log_message . PHP_EOL,
        FILE_APPEND
    ); // log to log file

    error_log ( PHP_EOL . PHP_EOL . $log_message . PHP_EOL , 4 ); // message is sent directly to the SAPI logging handler.
}

/**
 * This function detects which environment your web-app is running in
 * (i.e. one of Production, Development, Staging or Testing).
 *
 * NOTE: Make sure you edit ../config/env.php to return one of \SlimMvcTools\AppEnvironments::DEV,
 *       \SlimMvcTools\AppEnvironments::PRODUCTION, \SlimMvcTools\AppEnvironments::STAGING 
 *       or \SlimMvcTools\AppEnvironments::TESTING relevant to the environment you are
 *       installing your web-app.
 * 
 * @param string $app_path should be set to the absolute path of where your mvc app is installed just pass SMVC_APP_ROOT_PATH
 * @psalm-suppress MixedInferredReturnType
 */
function sMVC_DoGetCurrentAppEnvironment(string $app_path): string {

    static $current_env;

    if( !$current_env ) {

        $root_dir = $app_path. DIRECTORY_SEPARATOR;
        $env_file_path = $root_dir.'config'. DIRECTORY_SEPARATOR.'env.php';

        if( !file_exists($env_file_path) ) {

            $env_dist_file_path = "{$root_dir}config". DIRECTORY_SEPARATOR.'env-dist.php';
            sMVC_DisplayAndLogFrameworkFileNotFoundError(
                'Missing Environment Configuration File Error',
                $env_file_path,
                $env_dist_file_path,
                $app_path
            );
            exit;
        } // if( !file_exists($env_file) )

        /** @psalm-suppress MixedAssignment */
        $current_env = include $env_file_path;

    } // if( !$current_env )

    /** @psalm-suppress MixedReturnStatement */
    return $current_env;
}

function sMVC_PrependAction2ActionMethodName(string $action_method_name): string {

    if( strtolower( substr($action_method_name, 0, 6) ) !== "action") {

        $action_method_name = 'action'.  ucfirst($action_method_name);
    }

    return $action_method_name;
}

function sMVC_AddLangSelectionParamToUri(\Psr\Http\Message\UriInterface $uri, string $lang='en_US') : string {
    
    return sMVC_UriToString(
        sMVC_AddQueryStrParamToUri(
            $uri, 
            \SlimMvcTools\Controllers\BaseController::GET_QUERY_PARAM_SELECTED_LANG, 
            $lang
        )
    );
}
