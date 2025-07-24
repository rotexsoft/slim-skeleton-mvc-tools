<?php
declare(strict_types=1);

use \SlimMvcTools\ContainerKeys,
    \SlimMvcTools\AppSettingsKeys;

/**
 * Description of HtmlErrorRendererTest
 *
 * @author rotimi
 */
class HtmlErrorRendererTest extends \PHPUnit\Framework\TestCase {

    use BaseControllerDependenciesTrait;
    
    protected function setUp(): void {
        
        parent::setUp();
    }
    
    protected function tearDown(): void {
        
        parent::tearDown();
    }
    
    public function testThat___invoke_And_setDefaultErrorTitle_And_setDefaultErrorDescription_WorkAsExpected() {
        
        $html_renderer_no_template_file = new \SlimMvcTools\HtmlErrorRenderer('');
        $http_exception = new \Slim\Exception\HttpNotFoundException($this->newRequest(), 'Yoooooooo');
        $non_http_exception = new \Exception('Yaaaaaaaaa');
        $display_error_details = true;
        
        // Render HTTP Exception
        $result = $html_renderer_no_template_file($http_exception, $display_error_details);
        
        self::assertStringContainsString(
            $http_exception->getTitle(), 
            $result
        );
        self::assertStringContainsString(
            'A website error has occurred. Sorry for the temporary inconvenience.', 
            $result
        );
        self::assertStringContainsString(
            '<h2>Details</h2>', 
            $result
        );
        self::assertStringContainsString(
            sprintf('<div><strong>Type:</strong> %s</div>', get_class($http_exception)), 
            $result
        );
        self::assertStringContainsString(
            sprintf('<div><strong>Code:</strong> %s</div>', $http_exception->getCode()), 
            $result
        );
        self::assertStringContainsString(
            sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($http_exception->getMessage())), 
            $result
        );
        self::assertStringContainsString(
            sprintf('<div><strong>File:</strong> %s</div>', $http_exception->getFile()), 
            $result
        );
        self::assertStringContainsString(
            sprintf('<div><strong>Line:</strong> %s</div>', $http_exception->getLine()), 
            $result
        );
        self::assertStringContainsString(
            '<h2>Trace</h2>', 
            $result
        );
        self::assertStringContainsString(
            sprintf('<pre>%s</pre>', nl2br(htmlentities($http_exception->getTraceAsString()), false) ), 
            $result
        );
        //self::assertStringContainsString($needle, $result);
        
        // Render Non-HTTP Exception
        self::assertSame($html_renderer_no_template_file, $html_renderer_no_template_file->setDefaultErrorTitle('DefaultErrorTitle'));
        self::assertSame($html_renderer_no_template_file, $html_renderer_no_template_file->setDefaultErrorDescription('DefaultErrorDescription'));
        
        $result2 = $html_renderer_no_template_file($non_http_exception, $display_error_details);
        self::assertStringContainsString(
            'DefaultErrorTitle', 
            $result2
        );
        self::assertStringContainsString(
            'DefaultErrorDescription', 
            $result2
        );
        
        $result2b = $html_renderer_no_template_file($non_http_exception, false);
        self::assertStringContainsString(
            'DefaultErrorTitle', 
            $result2b
        );
        self::assertStringContainsString(
            'DefaultErrorDescription', 
            $result2b
        );
        
        $result3 = $html_renderer_no_template_file($http_exception, !$display_error_details);
        self::assertStringNotContainsString(
            'DefaultErrorTitle', 
            $result3
        );
        self::assertStringNotContainsString(
            'DefaultErrorDescription', 
            $result3
        );
        
        self::assertStringContainsString(
            $http_exception->getTitle(), 
            $result3
        );
        self::assertStringContainsString(
            $http_exception->getDescription(), 
            $result3
        );
        
        $container = $this->getContainer();
        
        // Test that when valid file is passed, the file is used to render
        $error_template_file = $container->get(ContainerKeys::APP_SETTINGS)[AppSettingsKeys::ERROR_TEMPLATE_FILE_PATH];
        $html_renderer_with_template_file = new \SlimMvcTools\HtmlErrorRenderer(
            $error_template_file
        );
        $result4 = $html_renderer_with_template_file($http_exception, !$display_error_details);
        self::assertStringContainsString(
            '<a href="#" onclick="window.history.go(-1)">Go Back Yooooooooooo</a>', 
            $result4
        );
    }
    
    public function testThatGetContainerAndSetContainerWorkAsExpected() {
        
        $html_renderer_no_template_file = new \SlimMvcTools\HtmlErrorRenderer('');
        
        self::assertNull($html_renderer_no_template_file->getContainer());
        
        $contaner1 = $this->getContainer();
        $contaner2 = $this->getContainer([AppSettingsKeys::DISPLAY_ERROR_DETAILS => false]);
        
        $html_renderer_no_template_file->setContainer($contaner1);
        self::assertSame($contaner1, $html_renderer_no_template_file->getContainer());
        self::assertNotSame($contaner2, $html_renderer_no_template_file->getContainer());
        
        $html_renderer_no_template_file->setContainer($contaner2);
        self::assertSame($contaner2, $html_renderer_no_template_file->getContainer());
        self::assertNotSame($contaner1, $html_renderer_no_template_file->getContainer());
    }
    
    public function testThatGetLocalizedTextWorkAsExpected() {
        
        $html_renderer_no_template_file = new \SlimMvcTools\HtmlErrorRenderer('');
        
        // No container set on renderer scenario
        self::assertEquals('', $html_renderer_no_template_file->getLocalizedText('NON_EXISTENT_TEXT_IN_LOCALE_FILE')); // should return default fall back text which is ''
        self::assertEquals('fallback text', $html_renderer_no_template_file->getLocalizedText('NON_EXISTENT_TEXT_IN_LOCALE_FILE', 'fallback text')); // should return specified fall back text
        
        $html_renderer_no_template_file->setContainer($this->getContainer());
        
        // Container set on renderer scenario
        self::assertEquals('', $html_renderer_no_template_file->getLocalizedText('NON_EXISTENT_TEXT_IN_LOCALE_FILE')); // should return default fall back text which is ''
        self::assertEquals('fallback text', $html_renderer_no_template_file->getLocalizedText('NON_EXISTENT_TEXT_IN_LOCALE_FILE', 'fallback text')); // should return specified fall back text
        self::assertEquals('Home', $html_renderer_no_template_file->getLocalizedText('main_template_text_home')); // should return text from locale file
        self::assertEquals('English', $html_renderer_no_template_file->getLocalizedText('base_controller_text_english', 'fallback text')); // should return text from locale file
    }
}
