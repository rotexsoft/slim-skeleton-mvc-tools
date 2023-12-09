<?php
declare(strict_types=1);

namespace SMVCTools\Tests\TestObjects;

/**
 * Description of AlwaysThrowExceptionOnLoginAuth
 *
 * @author rotimi
 */
class AlwaysThrowExceptionOnLoginAuth extends \Vespula\Auth\Auth {
    
    protected string $exceptionClass = \Vespula\Auth\Exception::class;
    protected string $exceptionMessage = 'Yaba daba doo!';


    public function setExceptionMessage(string $msg) {
        
        $this->exceptionMessage = $msg;
    }

    public function setExceptionClass(string $class) {
        
        $this->exceptionClass = $class;
    }
    
    public function login(array $credentials): void {
        
        $exceptionClass = $this->exceptionClass;
        
        throw new $exceptionClass($this->exceptionMessage);
    }
}
