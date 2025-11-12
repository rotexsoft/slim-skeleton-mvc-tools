<?php
declare(strict_types=1);

namespace SlimMvcTools;

/**
 * Description of HtmlErrorRenderer
 *
 * @author rotimi
 * @psalm-suppress UnusedClass
 */
class HtmlErrorRenderer extends \Slim\Error\Renderers\HtmlErrorRenderer {
    
    use BaseErrorRendererTrait;
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(protected string $path_to_error_template_file='') {}
    
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string {
                
        if ($displayErrorDetails) {
            
            $detailsText = $this->getLocalizedText('html_error_renderer_text_details', 'Details');
            
            $html = "<p>{$this->defaultErrorDescription}:</p>";
            $html .= "<h2>{$detailsText}</h2>";
            $html .= $this->renderExceptionFragment($exception);
            
        } else {
            
            $html = "<p>{$this->getErrorDescription($exception)}</p>";
        }

        return $this->renderHtmlBody($this->getErrorTitle($exception), $html);
    }
    
    public function renderHtmlBody(string $title = '', string $html = ''): string {
        
        if(
            $this->path_to_error_template_file === ''
            || !file_exists($this->path_to_error_template_file)
        ) {
            return parent::renderHtmlBody($title, $html);
        }
        
        $file_contents = file_get_contents($this->path_to_error_template_file);
        
        $appSettings = (array)$this->getContainerItem(ContainerKeys::APP_SETTINGS, []);
        /** @psalm-suppress MixedOperand */
        $appBasePath = \trim(''.($appSettings[AppSettingsKeys::APP_BASE_PATH] ?? ''));
        
        if(\str_ends_with($appBasePath, '/')) {
            
            // remove trailing forward slash
            $appBasePath = \substr($appBasePath, 0, -1);
        }
        
        return str_replace(
            ['{{{TITLE}}}', '{{{ERROR_HEADING}}}', '{{{ERROR_DETAILS}}}', '{{{APP_BASE_PATH}}}'], 
            [$title, $title, $html, $appBasePath], 
            ($file_contents === false) ? '' : $file_contents
        );
    }
    
    private function renderExceptionFragment(\Throwable $exception): string {
        
        $typeText = $this->getLocalizedText('html_error_renderer_text_type', 'Type');
        $codeText = $this->getLocalizedText('html_error_renderer_text_code', 'Code');
        $mssgText = $this->getLocalizedText('html_error_renderer_text_mssg', 'Message');
        $fileText = $this->getLocalizedText('html_error_renderer_text_file', 'File');
        $lineText = $this->getLocalizedText('html_error_renderer_text_line', 'Line');
        $traceText = $this->getLocalizedText('html_error_renderer_text_trace', 'Trace');
        
        $html = sprintf("<div><strong>{$typeText}:</strong> %s</div>", $exception::class);
        $code = $exception->getCode();
        $html .= sprintf("<div><strong>{$codeText}:</strong> %s</div>", $code);
        $html .= sprintf("<div><strong>{$mssgText}:</strong> %s</div>", nl2br(htmlentities($exception->getMessage()), false));
        $html .= sprintf("<div><strong>{$fileText}:</strong> %s</div>", $exception->getFile());
        $html .= sprintf("<div><strong>{$lineText}:</strong> %s</div>", $exception->getLine());
        $html .= "<h2>{$traceText}</h2>";

        return $html . sprintf('<pre>%s</pre>', nl2br(htmlentities($exception->getTraceAsString()), false) );
    }
}
