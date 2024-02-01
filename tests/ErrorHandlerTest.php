<?php
declare(strict_types=1);

/**
 * Description of ErrorHandlerTest
 *
 * @author rotimi
 */
class ErrorHandlerTest extends \PHPUnit\Framework\TestCase {
    
    use BaseControllerDependenciesTrait;

    protected function setUp(): void {
        
        parent::setUp();
    }
    
    protected function tearDown(): void {
        
        parent::tearDown();
    }
    
    public function testThat_getContainer_And_setContainer_WorkAsExpected() {
        
        $container = $this->getContainer();
        $error_handler = new \SlimMvcTools\ErrorHandler(
            \Slim\Factory\AppFactory::create()->getCallableResolver(), 
            \Slim\Factory\AppFactory::determineResponseFactory()
        );
        
        // always null container immediately after construction of new \SlimMvcTools\ErrorHandler
        self::assertNull($error_handler->getContainer());
        
        // setter must return the instance of \SlimMvcTools\ErrorHandler it was called on
        self::assertSame($error_handler , $error_handler->setContainer($container));
        
        // container just set is what the getter returns
        self::assertSame($container , $error_handler->getContainer());
    }
}
