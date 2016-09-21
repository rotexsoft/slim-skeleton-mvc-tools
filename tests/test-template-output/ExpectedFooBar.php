<?php
namespace Test\Space;
/**
 * 
 * Description of FooBar goes here
 *
 * 
 */
class FooBar extends SomeNameSpace\Controller2Extend
{   
    
    
    /**
     * 
     * Will be used in actionLogin() to construct the url to redirect to upon successful login,
     * if $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT] is not set.
     * 
     * @var string
     */
    protected $login_success_redirect_action = 'index';
    
    /**
     * 
     * Will be used in actionLogin() to construct the url to redirect to upon successful login,
     * if $_SESSION[static::SESSN_PARAM_LOGIN_REDIRECT] is not set.
     * 
     * @var string
     */
    protected $login_success_redirect_controller = 'foo-bar';
    
    /**
     * 
     * @param \Slim\App $app
     * @param string $controller_name_from_uri
     * @param string $action_name_from_uri
     * @param \Psr\Http\Message\ServerRequestInterface $req
     * @param \Psr\Http\Message\ResponseInterface $res
     * @param \Slim3MvcTools\Controllers\callable $not_found_handler
     * 
     */
    public function __construct(
        \Slim\App $app, $controller_name_from_uri, $action_name_from_uri, 
        \Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res,
        callable $not_found_handler        
    ) {
        parent::__construct($app, $controller_name_from_uri, $action_name_from_uri, $req, $res, $not_found_handler);
    }
    
    public function actionIndex() {
        
        //get the contents of the view first
        $view_str = $this->renderView('index.php');
        return $view_str;
        
        //uncomment and edit the line below to incorporate the view above into your app's template
        //return $this->renderLayout( 'main-template.php', ['content'=>$view_str] );
    }
}
