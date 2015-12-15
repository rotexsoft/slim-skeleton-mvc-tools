<?php
namespace __NAMESPACE_2_REPLACE__;
/**
 * 
 * Description of TEMPLTATE_CONTROLLER goes here
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
     * 
     * 
     * @param \Slim\App $app
     * @param string $controller_name_from_uri
     * @param string $action_name_from_uri
     * 
     */
    public function __construct(\Slim\App $app, $controller_name_from_uri, $action_name_from_uri) {
        
        parent::__construct($app, $controller_name_from_uri, $action_name_from_uri);
        
        //Prepend view folder for this controller. 
        //It takes precedence over the view folder for the base controller. 
        $path_2_view_files = __DIR__.DIRECTORY_SEPARATOR.'../views/{{TEMPLTATE_CONTROLLER_VIEW_FOLDER}}';
        $this->view_renderer->prependPath($path_2_view_files);
    }
    
    public function actionIndex() {
        
        //get the contents of the view first
        $view_str = $this->renderView('index.php');
        return $view_str;
        
        //uncomment and edit the line below to incorporate the view above into your app's template
        //return $this->renderLayout( 'main-template.php', ['content'=>$view_str] );
    }
}
