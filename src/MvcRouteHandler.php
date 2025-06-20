<?php
declare(strict_types=1);

namespace SlimMvcTools;

/**
 * Description of MvcRouteHandler
 *
 * @author rotimi
 * @psalm-suppress UnusedClass
 */
class MvcRouteHandler {
    
    public function __construct(
        protected \Slim\App $app, 
        protected string $default_controller_class, 
        protected string $default_controller_action, 
        protected bool $auto_prepend_action_to_method_name = false
    ) { }
    
    public function __invoke(
        \Psr\Http\Message\ServerRequestInterface $req,
        \Psr\Http\Message\ResponseInterface $resp,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $container = $this->app->getContainer();
        
        if(!($container instanceof \Psr\Container\ContainerInterface)) {
            
            /** @psalm-suppress InvalidOperand */
            $msg = "`".__FILE__."` on line ".__LINE__.": Null container retrieved from Slim App object.";
            throw new \Slim\Exception\HttpInternalServerErrorException($req, $msg);
        }

        // strip trailing forward slash
        /** @psalm-suppress MixedArgument */
        $params_str = isset($args['parameters'])? rtrim((string) $args['parameters'], '/') : '';

        // convert to array of parameters
        $params = ($params_str === '') ? [] : explode('/', $params_str);
        //////////////////////////////////////////////////////////////////////////////////////////////////////

        /** @psalm-suppress MixedArgument */
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
        /** @psalm-suppress MixedAssignment */
        $action_from_uri = $args['action'] ?? \SlimMvcTools\Functions\Str\toDashes($this->default_controller_action);
        
        $default_controller_parts = explode('\\', $this->default_controller_class);
        /** @psalm-suppress MixedAssignment */
        $controller_from_uri = $args['controller'] ?? \SlimMvcTools\Functions\Str\toDashes(array_pop($default_controller_parts));

        /** @psalm-suppress MixedArgument */
        $controller_obj = sMVC_CreateController($container, $controller_from_uri, $action_from_uri, $req, $resp);

        $this->assertMethodExistsOnControllerObj( $req, $controller_obj, $action_method );

        $pre_action_response = $controller_obj->preAction();
        $controller_obj->setResponse( $pre_action_response );

        try {
            // execute the controller's action
            /** @psalm-suppress MixedAssignment */
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
                // write the string into the response object as the response body
                $resp->getBody()->write($actn_res);

            } elseif ( $actn_res instanceof \Psr\Http\Message\ResponseInterface ) {

                $resp = $actn_res; // the action returned a Response object
            }
            
        } catch (\ArgumentCountError $e) {
            
            //400 Bad Request: Not enough arguments supplied in the uri to invoke the method above.
            /** @psalm-suppress InvalidOperand */
            $log_message = 
                    "`".__FILE__."` on line ".__LINE__
                    . sprintf(': Not enough arguments when calling `%s`(...) on an instance of `%s` for the uri `%s`.', $action_method, $controller_obj::class, $req->getUri()->__toString());
            
            /** @psalm-suppress PossiblyNullArgument */
            throw Utils::createSlimHttpExceptionWithLocalizedDescription(
                $this->app->getContainer(),
                SlimHttpExceptionClassNames::HttpBadRequestException,
                $req,
                $log_message,
                $e
            );
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
            /** @psalm-suppress InvalidOperand */
            $err_message = "`".__FILE__."` on line ".__LINE__.": Bad action name `{$action_method}`.";

            /** @psalm-suppress PossiblyNullArgument */
            throw Utils::createSlimHttpExceptionWithLocalizedDescription(
                $this->app->getContainer(),
                SlimHttpExceptionClassNames::HttpBadRequestException,
                $req,
                $err_message
            );
        }
    }
    
    protected function assertMethodExistsOnControllerObj(
        \Psr\Http\Message\ServerRequestInterface $req,
        \SlimMvcTools\Controllers\BaseController $controller_obj,
        string $action_method
    ):void {
        
        if( !method_exists($controller_obj, $action_method) ) {

            $controller_class_name = $controller_obj::class;

            // 404 Not Found: Action method does not exist in the controller object.
            /** @psalm-suppress InvalidOperand */
            $err_message = "`".__FILE__."` on line ".__LINE__
                .": The action method `{$action_method}` does not exist in class `{$controller_class_name}`.";
            
            /** @psalm-suppress PossiblyNullArgument */
            throw Utils::createSlimHttpExceptionWithLocalizedDescription(
                $this->app->getContainer(),
                SlimHttpExceptionClassNames::HttpNotFoundException,
                $req,
                $err_message
            );
        }
    }
}
