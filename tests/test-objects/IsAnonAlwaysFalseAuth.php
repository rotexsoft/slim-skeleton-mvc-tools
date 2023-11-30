<?php
declare(strict_types=1);

namespace SMVCTools\Tests\TestObjects;

/**
 * Description of IsAnonAlwaysTrueAuth
 *
 * @author Rotimi
 */
class IsAnonAlwaysFalseAuth extends \Vespula\Auth\Auth {
    
    public function isAnon(): bool {
        
        return false;
    }
}
