<?php
namespace SlimMvcTools\Controllers;

/**
 * 
 * Description of BaseController
 *
 * @author Rotimi Adegbamigbe
 * 
 */
class HttpMethodNotAllowedController extends BaseController
{
    // Not adding an action for Http 405 NotAllowed because an array of allowed methods must be 
    // supplied to such an action and that is not possible via a browser url. Slim or whatever 
    // framework will supply this array of allowed methods which will be paased to 
    // $this->generateNotAllowedResponse($methods, $req, $res)
}