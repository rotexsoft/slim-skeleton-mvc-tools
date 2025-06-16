<?php

/**
 * Description of NonDefaultPreAndPostActionController
 *
 * @author rotimi
 */
class NonDefaultPreAndPostActionController extends \SlimMvcTools\Controllers\BaseController {
    
    public function preAction(): \Psr\Http\Message\ResponseInterface {
        
        $response = parent::preAction();
        $response->getBody()->write('Running ' . __METHOD__ . '||');
        
        return $response;
    }
    
    public function postAction(\Psr\Http\Message\ResponseInterface $response): \Psr\Http\Message\ResponseInterface {
        
        $response2 = parent::postAction($response);
        
        $response2->getBody()->write('||Running ' . __METHOD__);
        
        return $response2;
    }
    
    public function actionIndex(): \Psr\Http\Message\ResponseInterface|string {
        
        $this->response->getBody()->write('Running ' . __METHOD__);
        
        return $this->response;
    }
}
