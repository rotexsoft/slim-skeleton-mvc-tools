<?php
declare(strict_types=1);

namespace SMVCTools\Tests\TestObjects;

/**
 * Description of StandAloneController
 *
 * @author rotimi
 */
class StandAloneController extends \SlimMvcTools\Controllers\BaseController {
    
    public function preAction(): \Psr\Http\Message\ResponseInterface {
        
        $resp = parent::preAction();
        
        $resp->getBody()->write('preAction: ');
        
        return $resp;
    }
    
    public function postAction(\Psr\Http\Message\ResponseInterface $response): \Psr\Http\Message\ResponseInterface {
        
        $resp =  parent::postAction($response);
        
        $resp->getBody()->write(' :postAction');
        
        return $resp;
    }
    
    public function actionHelloNoArgs() {
        
        $this->response->getBody()->write("Hello");
        
        return $this->response;
    }
    
    public function actionHelloReturnResp($first_name, $last_name) {
        
        $this->response->getBody()->write("Hello {$first_name}, {$last_name}");
        
        return $this->response;
    }
    
    public function actionHelloReturnStr($first_name, $last_name) {
        
        return "Hello {$first_name}, {$last_name}";
    }
}
