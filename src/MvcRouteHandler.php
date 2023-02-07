<?php
declare(strict_types=1);

namespace SlimMvcTools;

/**
 * Description of MvcRouteHandler
 *
 * @author rotimi
 */
class MvcRouteHandler {
    
    protected \Slim\App $app;
    protected bool $auto_prepend_action_to_method_name = false;
    protected string $default_controller_class;
    protected string $default_controller_action;

    public function __construct(
        \Slim\App $app,
        string $default_controller_class,
        string $default_controller_action,
        bool $auto_prepend_action_to_method_name
    ) {
        $this->app = $app;
        $this->auto_prepend_action_to_method_name = 
                    $auto_prepend_action_to_method_name;
        $this->default_controller_class = $default_controller_class;
        $this->default_controller_action = $default_controller_action;
    }
    
    public function __invoke(
        \Psr\Http\Message\ServerRequestInterface $req,
        \Psr\Http\Message\ResponseInterface $resp,
        ...$args
    ) {
        $container = $this->app->getContainer();

        // strip trailing forward slash
        $params_str = isset($args['parameters'])? rtrim($args['parameters'], '/') : '';

        // convert to array of parameters
        $params = empty($params_str) && mb_strlen($params_str, 'UTF-8') <= 0 ? [] : explode('/', $params_str);
        //////////////////////////////////////////////////////////////////////////////////////////////////////

        $action_method = 
            (isset($args['action']))
                ? \SlimMvcTools\Functions\Str\dashesToCamel($args['action'])
                : $this->default_controller_action;

        if( 
            $this->auto_prepend_action_to_method_name 
            && !\str_starts_with(\mb_strtolower($action_method, 'UTF-8'), 'action') 
        ) {
            // prepend the text action to the method's name
            $action_method = sMVC_PrependAction2ActionMethodName($action_method);
        }

        $this->validateMethodName($req, $action_method);
        
        $action_from_uri = isset($args['action'])
                            ? $args['action']
                            : \SlimMvcTools\Functions\Str\toDashes($this->default_controller_action);
        
        $default_controller_parts = explode('\\', $this->default_controller_class);
        $controller_from_uri = isset($args['controller'])
                            ? $args['controller']
                            : \SlimMvcTools\Functions\Str\toDashes(array_pop($default_controller_parts));

        $controller_obj = sMVC_CreateController($container, $controller_from_uri, $action_from_uri, $req, $resp);

        $this->assertMethodExistsOnControllerObj(
            $req, $controller_obj, $action_method
        );

        $pre_action_response = $controller_obj->preAction();
        $controller_obj->setResponse( $pre_action_response );

        // execute the controller's action
        $actn_res = 
            ($params === []) 
                ? $controller_obj->$action_method() // handle the following routes 
                                                    // '/' , 
                                                    // '/{controller}[/]'
                                                    // '/{controller}/{action}' 
                                                    // or '/{controller}/{action}/'
                
                : $controller_obj->$action_method(...$params); // handle this route
                                                               // '/{controller}/{action}[/{parameters:.+}]'

        // If we got this far, that means that the action method was successfully
        // executed on the controller object.
        if( is_string($actn_res) ) {

            $resp = $pre_action_response;
            $resp->getBody()->write($actn_res); // write the string in the response object as the response body

        } elseif ( $actn_res instanceof \Psr\Http\Message\ResponseInterface ) {

            $resp = $actn_res; // the action returned a Response object
        }

        return $controller_obj->postAction($resp);
    }

    protected function validateMethodName(
        \Psr\Http\Message\ServerRequestInterface $req,
        string $action_method
    ):void {
        
        $regex_4_valid_method_name = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';

        if( ! preg_match( $regex_4_valid_method_name, preg_quote($action_method, '/') ) ) {

            // A valid php class' method name starts with a letter or underscore,
            // followed by any number of letters, numbers, or underscores.

            // Make sure the controller name is a valid string usable as a class name
            // in php as defined in http://php.net/manual/en/language.oop5.basic.php
            // trigger 404 not found
            $err_message = "`".__FILE__."` on line ".__LINE__.": Bad action name `{$action_method}`.";

            throw new \Slim\Exception\HttpBadRequestException($req, $err_message);
        }
    }
    
    protected function assertMethodExistsOnControllerObj(
        \Psr\Http\Message\ServerRequestInterface $req,
        \SlimMvcTools\Controllers\BaseController $controller_obj,
        string $action_method
    ):void {
        
        if( !method_exists($controller_obj, $action_method) ) {

            $controller_class_name = get_class($controller_obj);

            // 404 Not Found: Action method does not exist in the controller object.
            $err_message = "`".__FILE__."` on line ".__LINE__
                .": The action method `{$action_method}` does not exist in class `{$controller_class_name}`.";

            throw new \Slim\Exception\HttpNotFoundException($req, $err_message);
        }
    }
}
