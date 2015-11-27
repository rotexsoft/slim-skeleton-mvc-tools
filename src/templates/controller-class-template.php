<?php

/**
 * 
 * Description of TEMPLTATE_CONTROLLER goes here
 *
 * 
 */
class TEMPLTATE_CONTROLLER extends \Slim3MvcTools\Controllers\BaseController
{   
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
