<?php
declare(strict_types=1);


namespace SMVCTools\Tests\TestObjects;

/**
 * Description of ControllerWithPublicDoLogin
 *
 * @author rotimi
 */
class ControllerWithPublicDoLogin extends \SlimMvcTools\Controllers\BaseController {
    
    public function doLoginPublic(\Vespula\Auth\Auth $auth, array $credentials, string &$success_redirect_path): string {
        
        return $this->doLogin($auth, $credentials, $success_redirect_path);
    }
}
