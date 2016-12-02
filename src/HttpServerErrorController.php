<?php
namespace Slim3MvcTools\Controllers;

/**
 * 
 * Description of BaseController
 *
 * @author Rotimi Adegbamigbe
 * 
 */
class HttpServerErrorController extends BaseController
{
    // Not adding an action for Server 500 error because an exception object must be supplied
    // to such an action and that is not possible via a browser url. Slim or whatever framework
    // will supply the exception object which will be paased to 
    // $this->generateServerErrorResponse($exception, $req, $res)
}