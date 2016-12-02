<?php
namespace __NAMESPACE_2_REPLACE__;
/**
 * 
 * Description of __TEMPLTATE_CONTROLLER__ goes here
 *
 * 
 */
class __TEMPLTATE_CONTROLLER__ extends __CONTROLLER_2_EXTEND__
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
    protected $login_success_redirect_controller = '__login_success_redirect_controller__';
    
    /**
     * 
     * @param \Interop\Container\ContainerInterface $container
     * @param string $controller_name_from_uri
     * @param string $action_name_from_uri
     * @param \Psr\Http\Message\ServerRequestInterface $req
     * @param \Psr\Http\Message\ResponseInterface $res
     * 
     */
    public function __construct(
        \Interop\Container\ContainerInterface $container, $controller_name_from_uri, $action_name_from_uri, 
        \Psr\Http\Message\ServerRequestInterface $req, \Psr\Http\Message\ResponseInterface $res
    ) {
        parent::__construct($container, $controller_name_from_uri, $action_name_from_uri, $req, $res);
    }
    
    public function actionIndex() {
        
        //get the contents of the view first
        $view_str = $this->renderView('index.php');
        return $view_str;
        
        //uncomment and edit the line below to incorporate the view above into your app's template
        //return $this->renderLayout( $this->layout_template_file_name, ['content'=>$view_str] );
    }
    
    public function preAction() {
        
        parent::preAction();
        
        // add code that you need to be executed before each controller action method is executed
    }
    
    public function postAction() {
        
        parent::postAction();
        
        // add code that you need to be executed after each controller action method is executed
    }
}