<?php
declare(strict_types=1);

/**
 * Description of MvcRouteHandlerTest
 *
 * @author rotimi
 */
class MvcRouteHandlerTest extends \PHPUnit\Framework\TestCase  {

    use BaseControllerDependenciesTrait;
    
    protected function setUp(): void {
        
        parent::setUp();
    }
    
    protected function tearDown(): void {
        
        parent::tearDown();
    }
    
    public function testThat___invoke_WorksAsExpected() {
        
        $container = $this->getContainer();
        \Slim\Factory\AppFactory::setContainer($container);
        $app = \Slim\Factory\AppFactory::create();
        $auto_prepend_action_to_method_name = true;
        
        $mvc_route_handler1 = new \SlimMvcTools\MvcRouteHandler(
            $app, \SlimMvcTools\Controllers\BaseController::class,
            'actionIndex', $auto_prepend_action_to_method_name
        );
        
        $args1 = [
            'parameters' => 'John/Doe',
            'action' => 'hello-return-resp',
            'controller' => 'stand-alone-controller'
        ];
        
        $args2 = [
            'parameters' => 'John/Doe',
            'action' => 'hello-return-str',
            'controller' => 'stand-alone-controller'
        ];
        
        $args2A = [
            'parameters' => '',
            // should lead to invocation of default action actionIndex
            //'action' => 'action-hello-no-args',
            'controller' => 'stand-alone-controller'
        ];
        
        $args3 = [
            'parameters' => 'John/Doe',
            'action' => 'action-hello-return-resp', // should not autoprepend because already prefixed with action
            'controller' => 'stand-alone-controller'
        ];
        
        $args4 = [
            'parameters' => 'John/Doe',
            'action' => 'action-hello-return-str', // should not autoprepend because already prefixed with action
            'controller' => 'stand-alone-controller'
        ];
        
        $args5 = [
            'parameters' => '',
            'action' => 'action-hello-no-args', // should not autoprepend because already prefixed with action
            'controller' => 'stand-alone-controller'
        ];
        
        ////////////////////////////////////////////////////////////////////////
        // auto prepend action
        ////////////////////////////////////////////////////////////////////////
        /** @var \Psr\Http\Message\ResponseInterface $resp1 */
        $resp1 = $mvc_route_handler1($this->newRequest(), $this->newResponse(''), $args1);
        $resp1->getBody()->rewind();
        self::assertEquals($resp1->getBody().'', 'preAction: Hello John, Doe :postAction');
        
        $resp2 = $mvc_route_handler1($this->newRequest(), $this->newResponse(''), $args2);
        $resp2->getBody()->rewind();
        self::assertEquals($resp2->getBody().'', 'preAction: Hello John, Doe :postAction');
        
        $resp2A = $mvc_route_handler1($this->newRequest(), $this->newResponse(''), $args2A);
        $resp2A->getBody()->rewind();
        self::assertEquals($resp2A->getBody().'', 'preAction: Invoked ' . \SMVCTools\Tests\TestObjects\StandAloneController::class . '::actionIndex :postAction');
        
        $resp3 = $mvc_route_handler1($this->newRequest(), $this->newResponse(''), $args3);
        $resp3->getBody()->rewind();
        self::assertEquals($resp3->getBody().'', 'preAction: Hello John, Doe :postAction');
        
        $resp4 = $mvc_route_handler1($this->newRequest(), $this->newResponse(''), $args4);
        $resp4->getBody()->rewind();
        self::assertEquals($resp4->getBody().'', 'preAction: Hello John, Doe :postAction');
        
        ////////////////////////////////////////////////////////////////////////
        // DONT auto prepend action
        ////////////////////////////////////////////////////////////////////////
        $mvc_route_handler2 = new \SlimMvcTools\MvcRouteHandler(
            $app, \SlimMvcTools\Controllers\BaseController::class,
            'actionIndex', !$auto_prepend_action_to_method_name
        );
        
        $resp5 = $mvc_route_handler2($this->newRequest(), $this->newResponse(''), $args3);
        $resp5->getBody()->rewind();
        self::assertEquals($resp5->getBody().'', 'preAction: Hello John, Doe :postAction');
        
        $resp6 = $mvc_route_handler2($this->newRequest(), $this->newResponse(''), $args4);
        $resp6->getBody()->rewind();
        self::assertEquals($resp6->getBody().'', 'preAction: Hello John, Doe :postAction');
        
        // route with only controller and action and no parameters
        $resp7 = $mvc_route_handler2($this->newRequest(), $this->newResponse(''), $args5);
        $resp7->getBody()->rewind();
        self::assertEquals($resp7->getBody().'', 'preAction: Hello :postAction');
        
        $this->expectException(\Slim\Exception\HttpNotFoundException::class);
        
        // auto prepend action off will lead to method not found on controller
        $mvc_route_handler2($this->newRequest(), $this->newResponse(''), $args1);
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testThat___invoke_WorksAsExpected2() {
        
        $this->expectException(\Slim\Exception\HttpInternalServerErrorException::class);
        
        //App with null container
        $app = \Slim\Factory\AppFactory::create();
        $auto_prepend_action_to_method_name = true;
        
        $mvc_route_handler1 = new \SlimMvcTools\MvcRouteHandler(
            $app, \SlimMvcTools\Controllers\BaseController::class,
            'actionIndex', $auto_prepend_action_to_method_name
        );
        
        $args1 = [
            'parameters' => '',
            'controller' => 'base-controller'
        ];
        
        $mvc_route_handler1($this->newRequest(), $this->newResponse(), $args1);
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testThat___invoke_WorksAsExpected3() {
        
        // too few arguments passed to controller method in the uri
        $this->expectException(\Slim\Exception\HttpBadRequestException::class);
        $message = '/.*: Not enough arguments when calling `actionHelloReturnStr`.*/';
        $this->expectExceptionMessageMatches($message);
        
        $container = $this->getContainer();
        \Slim\Factory\AppFactory::setContainer($container);
        $app = \Slim\Factory\AppFactory::create();
        
        
        $auto_prepend_action_to_method_name = true;
        
        $mvc_route_handler1 = new \SlimMvcTools\MvcRouteHandler(
            $app, \SlimMvcTools\Controllers\BaseController::class,
            'actionIndex', $auto_prepend_action_to_method_name
        );
        
        $args = [
            'parameters' => 'John', // only one parameter instead of two like John/Doe
            'action' => 'action-hello-return-str',
            'controller' => 'stand-alone-controller'
        ];        
        $mvc_route_handler1($this->newRequest(), $this->newResponse(), $args);
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testThat___invoke_WorksAsExpected4() {
        
        // When a conntroller method that throws an exception is called
        $this->expectException(\Slim\Exception\HttpInternalServerErrorException::class);
        $message = '/.*: Error occured when calling `actionThrowException`.*/';
        $this->expectExceptionMessageMatches($message);
        
        $container = $this->getContainer();
        \Slim\Factory\AppFactory::setContainer($container);
        $app = \Slim\Factory\AppFactory::create();
        
        
        $auto_prepend_action_to_method_name = true;
        
        $mvc_route_handler1 = new \SlimMvcTools\MvcRouteHandler(
            $app, \SlimMvcTools\Controllers\BaseController::class,
            'actionIndex', $auto_prepend_action_to_method_name
        );
        
        $args = [
            'parameters' => '',
            'action' => 'action-throw-exception', // method that throws exception
            'controller' => 'stand-alone-controller'
        ];        
        $mvc_route_handler1($this->newRequest(), $this->newResponse(), $args);
    }
    
    public function testThat_validateMethodName_WorksAsExpected() {
        
        $this->expectException(\Slim\Exception\HttpBadRequestException::class);
        
        $container = $this->getContainer();
        \Slim\Factory\AppFactory::setContainer($container);
        $app = \Slim\Factory\AppFactory::create();
        $auto_prepend_action_to_method_name = true;
        
        $mvc_route_handler1 = new \SlimMvcTools\MvcRouteHandler(
            $app, \SlimMvcTools\Controllers\BaseController::class,
            'actionIndex', $auto_prepend_action_to_method_name
        );
        
        $args1 = [
            'parameters' => '',
            'action' => '*1-bad-action-name',
            'controller' => 'base-controller'
        ];
        
        $mvc_route_handler1($this->newRequest(), $this->newResponse(), $args1);
    }
    
    public function testThat_assertMethodExistsOnControllerObj_WorksAsExpected() {
        
        $this->expectException(\Slim\Exception\HttpNotFoundException::class);
        
        $container = $this->getContainer();
        \Slim\Factory\AppFactory::setContainer($container);
        $app = \Slim\Factory\AppFactory::create();
        $auto_prepend_action_to_method_name = true;
        
        $mvc_route_handler1 = new \SlimMvcTools\MvcRouteHandler(
            $app, \SlimMvcTools\Controllers\BaseController::class,
            'actionIndex', $auto_prepend_action_to_method_name
        );
        
        $args1 = [
            'parameters' => '',
            'action' => 'non-existent-action',
            'controller' => 'base-controller'
        ];
        
        $mvc_route_handler1($this->newRequest(), $this->newResponse(), $args1);
    }
}
