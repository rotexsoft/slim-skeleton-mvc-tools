<?php
namespace Slim3MvcTools\Controllers;

/**
 * 
 * Description of BaseController
 *
 * @author Rotimi Adegbamigbe
 * 
 */
class HttpNotFoundController extends BaseController
{
    public function actionHttpNotFound( $_404_page_content=null, $_404_additional_log_message=null, $render_layout=true) {
        
        return parent::actionHttpNotFound($_404_page_content, $_404_additional_log_message, $render_layout);
    }
}