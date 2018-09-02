<?php
/**
 * 
 * Creates a controller object or returns a Respond object containing a not found page.
 *  
 * The controller class must be \Slim3MvcTools\Controllers\BaseController or one of its sub-classes
 * 
 * @param \Interop\Container\ContainerInterface $container
 * @param string $controller_name_from_url
 * @param string $action_name_from_url
 * @param \Psr\Http\Message\ServerRequestInterface $request
 * @param \Psr\Http\Message\ResponseInterface $response
 * 
 * @return \Slim3MvcTools\Controllers\BaseController|\Psr\Http\Message\ResponseInterface 
 *          an instance of \Slim3MvcTools\Controllers\BaseController or its sub-class or
 *          an instance \Psr\Http\Message\ResponseInterface containing the not found 
 *          page.
 */
function s3MVC_CreateController(
    \Interop\Container\ContainerInterface $container, 
    $controller_name_from_url, 
    $action_name_from_url,
    \Psr\Http\Message\ServerRequestInterface $request, 
    \Psr\Http\Message\ResponseInterface $response
) {    
    $notFoundHandler = $container->has('notFoundHandler') 
                                ? $container->get('notFoundHandler') 
                                : null;
        
    $controller_class_name = \Slim3MvcTools\Functions\Str\dashesToStudly($controller_name_from_url);
    $regex_4_valid_class_name = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

    if( 
        !preg_match( $regex_4_valid_class_name, preg_quote($controller_class_name, '/') )
    ) {
        //A valid php class name starts with a letter or underscore, followed by 
        //any number of letters, numbers, or underscores.

        $extra_log_message = "`" . __FILE__ . "` on line " . __LINE__ . ": Bad controller name `{$controller_class_name}`";
        
        if ( !is_null($notFoundHandler) ) {
            
            //Make sure the controller name is a valid string usable as a class name
            //in php as defined in http://php.net/manual/en/language.oop5.basic.php
            //trigger 404 not found
            return $notFoundHandler($request, $response, null, $extra_log_message);
            
        } else {
            
            throw new \Exception($extra_log_message);
        }
    } 

    if( !class_exists($controller_class_name) ) {
        
        if( $container->has('namespaces_for_controllers') ) {
            
            $namespaces_4_controllers = $container->get('namespaces_for_controllers');

            //try to prepend name space
            foreach($namespaces_4_controllers as $namespace_4_controllers) {

                if( class_exists($namespace_4_controllers.$controller_class_name) ) {

                    $controller_class_name = $namespace_4_controllers.$controller_class_name;
                    break;
                }
            }
        }
        
        //class still doesn't exist
        if( !class_exists($controller_class_name) ) {

            //404 Not Found: Controller class not found.
            $extra_log_message = "`".__FILE__."` on line ".__LINE__.": Class `{$controller_class_name}` does not exist.";
            
            if ( !is_null($notFoundHandler) ) {

                //Make sure the controller name is a valid string usable as a class name
                //in php as defined in http://php.net/manual/en/language.oop5.basic.php
                //trigger 404 not found
                return $notFoundHandler($request, $response, null, $extra_log_message);

            } else {

                throw new \Exception($extra_log_message);
            }
        }
    }

    //Create the controller object
    return new $controller_class_name($container, $controller_name_from_url, $action_name_from_url, $request, $response);
}

/**
 * 
 * @param \Vespula\Auth\Auth $auth
 * @return string containing current authentication status info
 */
function s3MVC_DumpAuthinfo(\Vespula\Auth\Auth $auth) {

    return 'Login Status: ' . $auth->getSession()->getStatus() . PHP_EOL
         . 'Logged in Person\'s Username: ' . $auth->getUsername().PHP_EOL
         . 'Logged in User\'s Data: ' . PHP_EOL . print_r($auth->getUserdata(), true);
}

/**
 * 
 * @param mixed $v variable or expression to dump
 */
function s3MVC_DumpVar($v) {

    $v = (!is_string($v)) ? (new \SebastianBergmann\Exporter\Exporter())->export($var) : $v;
    echo "<pre>$v</pre>";
}

/**
 * 
 * Returns the base path segment of the URI.
 * It performs the same function as \Slim\Http\Uri::getBasePath()
 * You are strongly advised to use this function instead of 
 * \Slim\Http\Uri::getBasePath(), in order to ensure that your 
 * app will be compatible with other PSR-7 implementations because
 * \Slim\Http\Uri::getBasePath() is not a PSR-7 method.
 * 
 * @return string
 */
function s3MVC_GetBaseUrlPath() {

    static $server, $base_path, $has_been_computed;

    if( !$server ) {

        //copy / capture the super global only once
        $server = s3MVC_GetSuperGlobal('server');
    }

    if( !$base_path && !$has_been_computed ) {

        $base_path = '';
        $has_been_computed = true;
        $requestScriptName = parse_url($server['SCRIPT_NAME'], PHP_URL_PATH);
        $requestScriptDir = dirname($requestScriptName);
        
        // parse_url() requires a full URL. As we don't extract the domain name or scheme,
        // we use a stand-in.
        $requestUri = parse_url( 'http://example.com' . $server['REQUEST_URI'], PHP_URL_PATH);

        if (strcasecmp($requestUri, $requestScriptName) === 0) {

            $base_path = $requestScriptName;

        } elseif ($requestScriptDir !== '/' && stripos($requestUri, $requestScriptDir) === 0) {

            $base_path = $requestScriptDir;
        }
    }

    return $base_path;
}

/**
 * 
 * Generates a link prepended with s3MVC_GetBaseUrlPath().
 * Can be used for generating values for the href attribute of an a or link tag, or the src 
 * atrribute of a script tag, etc.
 * 
 * @param string $path
 * @return string
 */
function s3MVC_MakeLink($path){
    
    return s3MVC_GetBaseUrlPath(). '/'.ltrim($path, '/');
}

/**
 * 
 * This function stores a snapshot of the following super globals $_SERVER, $_GET,
 * $_POST, $_FILES, $_COOKIE, $_SESSION & $_ENV and then returns the stored values
 * on subsequent calls. (In the case of $_SESSION, a reference to it is kept so 
 * that modifying s3MVC_GetSuperGlobal('session') will also modify $_SESSION). 
 * If a session has not been started s3MVC_GetSuperGlobal('session') will always
 * return null, likewise s3MVC_GetSuperGlobal('session', 'some_key') will always
 * return $default_val.
 * 
 * IT IS STRONGLY RECOMMENDED THAY YOU USE LIBRARIES LIKE aura/session 
 * (https://github.com/auraphp/Aura.Session) TO WORK WITH $_SESSION.
 * USING s3MVC_GetSuperGlobal('session') IS HIGHLY DISCOURAGED.
 * 
 * @param string $global_name the name (case-insensitive) of a any of the super 
 *                            globals mentioned above (excluding the $_). For 
 *                            example 'Post', 'pOst', etc.
 *                            s3MVC_GetSuperGlobal('get') === s3MVC_GetSuperGlobal('gEt'), etc.
 * 
 * @param string $key a key in the specified super global. For example $_GET['id']
 *                    is equivalent to s3MVC_GetSuperGlobal('get', 'id');
 * 
 * @param string $default_val the value to return if $key is not an actual key in
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
function s3MVC_GetSuperGlobal($global_name='', $key='', $default_val='') {

    static $super_globals;

    $is_session_started = (session_status() === PHP_SESSION_ACTIVE);

    if( !$super_globals ) {

        $super_globals = [];
        $super_globals['server'] = isset($_SERVER)? $_SERVER : []; //copy
        $super_globals['get'] = isset($_GET)? $_GET : []; //copy
        $super_globals['post'] = isset($_POST)? $_POST : []; //copy
        $super_globals['files'] = isset($_FILES)? $_FILES : []; //copy
        $super_globals['cookie'] = isset($_COOKIE)? $_COOKIE : []; //copy
        $super_globals['env'] = isset($_ENV)? $_ENV : []; //copy

        if( $is_session_started ) {

            $super_globals['session'] =& $_SESSION; //obtain a reference

        } else {

            $super_globals['session'] = null;
        }
    }

    if( empty($global_name) ) {

        //return everything
        return $super_globals;
    }

    //normalize the global name
    $global_name = strtolower($global_name);

    if( strpos($global_name, '$_') === 0 ) {

        $global_name = substr($global_name, 2);
    }

    if( empty($key) ) {

        //return everything for the specified global
        return array_key_exists($global_name, $super_globals)
                                    ? $super_globals[$global_name] : [];
    }

    if( !$is_session_started && $global_name === 'session' ) {

        //return the default value because $super_globals['session'] === null
        return $default_val;
    }

    //return value of the specified key in the specified global or the default value
    return array_key_exists($key, $super_globals[$global_name])
                                ? $super_globals[$global_name][$key] : $default_val;

}

/**
 * 
 * Converts a uri object to a string in the format <scheme>://<server_address>/<path>?<query_string>#<fragment>
 * 
 * @param \Psr\Http\Message\UriInterface $uri uri object to be converted to a string
 * 
 * @return string the string represntation of the uri object. 
 *                Eg. http://someserver.com/controller/action
 */
function s3MVC_UriToString(\Psr\Http\Message\UriInterface $uri) {
    
    $scheme = $uri->getScheme();
    $authority = $uri->getAuthority();
    $basePath = s3MVC_GetBaseUrlPath();
    $path = $uri->getPath();
    $query = $uri->getQuery();
    $fragment = $uri->getFragment();

    $path = $basePath . '/' . ltrim($path, '/');

    return ($scheme ? $scheme . ':' : '')
        . ($authority ? '//' . $authority : '')
        . $path
        . ($query ? '?' . $query : '')
        . ($fragment ? '#' . $fragment : '');
}

/**
 * 
 * Adds a query string parameter key/value pair to a uri object.
 * 
 * Given a uri object $uri1 representing http://someserver.com/controller/action?param1=val1 
 * s3MVC_addQueryStrParamToUri($uri1, 'param2', 'val2') will return a new uri object representing
 * http://someserver.com/controller/action?param1=val1&param2=val2
 * 
 * @param \Psr\Http\Message\UriInterface $uri
 * @param string $param_name
 * @param string $param_value
 * 
 * @return \Psr\Http\Message\UriInterface
 */
function s3MVC_addQueryStrParamToUri(
    \Psr\Http\Message\UriInterface $uri, $param_name, $param_value
) {
    $query_params = [];
    
    parse_str($uri->getQuery(), $query_params); // Extract existing query string params to an array
    
    $query_params[$param_name] = $param_value; // Add new param the query string params array
    
    return $uri->withQuery(http_build_query($query_params)); // return a uri object with updated query params
}

/**
 * 
 * @param \Psr\Http\Message\ServerRequestInterface $req
 * @param array $request_attribute_keys_to_skip
 * @param bool $skip_req_attribs
 * @param bool $skip_req_body
 * @param bool $skip_req_cookie_params
 * @param bool $skip_req_headers
 * @param bool $skip_req_method
 * @param bool $skip_req_proto_ver
 * @param bool $skip_req_query_params
 * @param bool $skip_req_target
 * @param bool $skip_req_server_params
 * @param bool $skip_req_uploaded_files
 * @param bool $skip_req_uri
 * 
 * @return string
 */
function s3MVC_psr7RequestObjToString(
    \Psr\Http\Message\ServerRequestInterface $req, 
    array $request_attribute_keys_to_skip=['route','routeInfo'],
    $skip_req_attribs=false,
    $skip_req_body=false,
    $skip_req_cookie_params=false,
    $skip_req_headers=false,
    $skip_req_method=false,
    $skip_req_proto_ver=false,
    $skip_req_query_params=false,
    $skip_req_target=false,
    $skip_req_server_params=false,
    $skip_req_uploaded_files=false,
    $skip_req_uri=false
) { 
    $uploaded_files_as_str = 
        empty($req->getUploadedFiles()) ? 
            null : 
            array_reduce( 
                $req->getUploadedFiles(),
                function($prev, $curr) {  return $prev .= s3MVC_psr7UploadedFileToString($curr) . PHP_EOL; }, 
                ''
            )
        ;

    $request_attributes = empty($req->getAttributes())? [] : $req->getAttributes();

    $attribs_filterer = function ($val, $key) use (&$request_attributes) {
        if( array_key_exists($val, $request_attributes) ) { unset($request_attributes[$val]); }
    };

    array_walk($request_attribute_keys_to_skip, $attribs_filterer);

    return (
                (!$skip_req_attribs)
                ?
                    "[[Request Attributes]]:" . PHP_EOL
                   . print_r( $request_attributes, true )
                   . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
            .
            (
                (!$skip_req_body)
                ?
                    PHP_EOL . "[[Request Body]]:" . PHP_EOL
                    . $req->getBody()->__toString()
                    . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
            .
            (
                (!$skip_req_cookie_params)
                ?
                    PHP_EOL . "[[Request Cookie Params]]:" . PHP_EOL
                    . var_export( $req->getCookieParams(), true )
                    . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
            .
            (
                (!$skip_req_headers)
                ?
                    PHP_EOL . "[[Request Headers]]:" . PHP_EOL
                    . var_export( $req->getHeaders(), true )
                    . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
            .
            (
                (!$skip_req_method)
                ?
                    PHP_EOL . "[[Request Method]]: "
                    . $req->getMethod()
                    . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
            .
            (
                (!$skip_req_proto_ver)
                ?
                    PHP_EOL . "[[Request Protocol Version]]: "
                    . $req->getProtocolVersion()
                    . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
            .
            (
                (!$skip_req_query_params)
                ?
                    PHP_EOL . "[[Request Query Params]]:" . PHP_EOL
                    . var_export( $req->getQueryParams(), true )
                    . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
            .
            (
                (!$skip_req_target)
                ?
                    PHP_EOL . "[[Request Target]]: "
                    . $req->getRequestTarget()
                    . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
            .
            (
                (!$skip_req_server_params)
                ?
                    PHP_EOL . "[[Request Server Params]]:" . PHP_EOL
                    . var_export( $req->getServerParams(), true )
                    . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
            .
            (
                (!$skip_req_uploaded_files)
                ?
                    PHP_EOL . "[[Request Uploaded Files]]:" . PHP_EOL
                    . var_export( $uploaded_files_as_str, true )
                    . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
            .
            (
                (!$skip_req_uri)
                ?
                    PHP_EOL . "[[Request Uri]]:" . PHP_EOL
                    . var_export( s3MVC_UriToString($req->getUri()), true )
                    . PHP_EOL . PHP_EOL . "<<=================================================>>"
                :
                    ''
            )
        ;
}
    
function s3MVC_psr7UploadedFileToString(\Psr\Http\Message\UploadedFileInterface $file) {
        
    return "[[Uploaded File Client Filename]]: "
           . $file->getClientFilename()

           . PHP_EOL . PHP_EOL . "<<=================================================>>"
           . PHP_EOL . "[[Uploaded Client Media Type]]: "
           . $file->getClientMediaType()

           . PHP_EOL . PHP_EOL . "<<=================================================>>"
           . PHP_EOL . "[[Uploaded File Size in Bytes]]: "
           . $file->getSize()

           . PHP_EOL . PHP_EOL . "<<=================================================>>"
           . PHP_EOL . "[[Uploaded File Contents]]:" . PHP_EOL
           . var_export($file->getStream()->__toString(), true)

           . PHP_EOL . PHP_EOL . "<<=================================================>>"
           . PHP_EOL . "[[Uploaded File Error(s) If Any]]: "
           . $file->getError()                
        ;
}
