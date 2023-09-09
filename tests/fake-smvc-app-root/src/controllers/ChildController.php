<?php
declare(strict_types=1);

namespace SMVCTools\Tests\TestObjects;

/**
 * Description of ChildController
 *
 * @author rotimi
 */
class ChildController extends \SlimMvcTools\Controllers\BaseController {

    protected string $controller_name_from_uri = 'child-controller';
    
    public bool $is_logged_in = false;


    /**
     * Override the parent implementation to help simulate login in tests
     */
    public function isLoggedIn(): bool {
        
        return $this->is_logged_in;
    }
}
