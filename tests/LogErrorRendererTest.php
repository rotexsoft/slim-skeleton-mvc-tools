<?php
declare(strict_types=1);

/**
 * Description of LogErrorRendererTest
 *
 * @author rotimi
 */
class LogErrorRendererTest extends \PHPUnit\Framework\TestCase  {

    use BaseControllerDependenciesTrait;
    
    protected function setUp(): void {
        
        parent::setUp();
    }
    
    protected function tearDown(): void {
        
        parent::tearDown();
    }
    
    public function testThat___invoke_WorksAsExpected() {
        
        $nl = PHP_EOL;
        $log_error_renderer = new \SlimMvcTools\LogErrorRenderer();
        $http_exception = new \Slim\Exception\HttpNotFoundException($this->newRequest(), 'Yoooooooo');
        $non_http_exception = new \Exception('Yaaaaaaaaa');
        $exception_with_previous_exception = new \Exception('Bwwwaaaaaaaaa', 5, $non_http_exception);
        $display_error_details = true;
        
        // Test that it works as expected with HTTP exception & display errors === false
        $result = $log_error_renderer($http_exception, !$display_error_details);
        self::assertStringContainsString(
            "{$http_exception->getTitle()}{$nl}", 
            $result
        );
        self::assertStringContainsString(
            sprintf('Request Uri: %s ', $http_exception->getRequest()->getUri()->__toString() . $nl), 
            $result
        );
        self::assertStringNotContainsString(
            "Type:", 
            $result
        );
        self::assertStringNotContainsString(
            "Code:", 
            $result
        );
        self::assertStringNotContainsString(
            "Message:", 
            $result
        );
        self::assertStringNotContainsString(
            "File:", 
            $result
        );
        self::assertStringNotContainsString(
            "Line:", 
            $result
        );
        self::assertStringNotContainsString(
            "Trace:", 
            $result
        );
        self::assertStringNotContainsString(
            "Previous Error:", 
            $result
        );
        
        // Test that it works as expected with non-HTTP exception & display errors === false
        $result2 = $log_error_renderer($exception_with_previous_exception, !$display_error_details);
        self::assertStringContainsString(
            "Slim Application Error{$nl}", 
            $result2
        );
        self::assertStringNotContainsString(
            'Request Uri:', 
            $result2
        );
        self::assertStringNotContainsString(
            "Previous Error:", 
            $result2
        );
        
        $log_error_renderer->setDefaultErrorTitle('APP Error');
        $result3 = $log_error_renderer($exception_with_previous_exception, !$display_error_details);
        self::assertStringContainsString(
            "APP Error{$nl}", 
            $result3
        );
        self::assertStringNotContainsString(
            "Slim Application Error{$nl}", 
            $result3
        );
        self::assertStringNotContainsString(
            'Request Uri:', 
            $result3
        );
        self::assertStringNotContainsString(
            "Previous Error:", 
            $result3
        );
        
        // Test that it works as expected with non-HTTP exception & display errors === true
        $result4 = $log_error_renderer($exception_with_previous_exception, $display_error_details);
        self::assertStringContainsString(
            "APP Error{$nl}", 
            $result4
        );
        self::assertStringNotContainsString(
            'Request Uri:', 
            $result4
        );
        self::assertStringContainsString(
            sprintf("Type: %s{$nl}", get_class($exception_with_previous_exception)), 
            $result4
        );
        self::assertStringContainsString(
            sprintf("Code: %s{$nl}", $exception_with_previous_exception->getCode()), 
            $result4
        );
        self::assertStringContainsString(
            sprintf("Message: %s{$nl}", htmlentities($exception_with_previous_exception->getMessage())), 
            $result4
        );
        self::assertStringContainsString(
            sprintf("File: %s{$nl}", $exception_with_previous_exception->getFile()), 
            $result4
        );
        self::assertStringContainsString(
            sprintf("Line: %s{$nl}", $exception_with_previous_exception->getLine()), 
            $result4
        );
        self::assertStringContainsStringIgnoringCase(
            sprintf("Trace: %s{$nl}", $exception_with_previous_exception->getTraceAsString()), 
            $result4
        );
        self::assertStringContainsString(
            "Previous Error:", 
            $result4
        );
        
        self::assertStringContainsString(
            sprintf("Type: %s{$nl}", get_class($exception_with_previous_exception->getPrevious())), 
            $result4
        );
        self::assertStringContainsString(
            sprintf("Code: %s{$nl}", $exception_with_previous_exception->getPrevious()->getCode()), 
            $result4
        );
        self::assertStringContainsString(
            sprintf("Message: %s{$nl}", htmlentities($exception_with_previous_exception->getPrevious()->getMessage())), 
            $result4
        );
        self::assertStringContainsString(
            sprintf("File: %s{$nl}", $exception_with_previous_exception->getPrevious()->getFile()), 
            $result4
        );
        self::assertStringContainsString(
            sprintf("Line: %s{$nl}", $exception_with_previous_exception->getPrevious()->getLine()), 
            $result4
        );
        self::assertStringContainsStringIgnoringCase(
            sprintf("Trace: %s{$nl}", $exception_with_previous_exception->getPrevious()->getTraceAsString()), 
            $result4
        );
    }
}
