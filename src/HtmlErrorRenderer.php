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
    
    protected string $path_to_error_template_file = '';
    
    public function __construct(string $full_path_to_error_template_file) {
        
        $this->path_to_error_template_file = $full_path_to_error_template_file;
    }
    
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string {
        
        $requestLine = '';
        
        if ($displayErrorDetails) {
            
            $html = "<p>{$this->defaultErrorDescription}:</p>";
            $html .= '<h2>Details</h2>';
            $html .= $requestLine;
            $html .= $this->renderExceptionFragment($exception);
            
        } else {
            
            $html = "<p>{$this->getErrorDescription($exception)}</p>";
            $html .= $requestLine;
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
        
        return sprintf($file_contents, $title, $title, $html);
    }
    
    private function renderExceptionFragment(\Throwable $exception): string {
        
        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($exception));
        $code = $exception->getCode();
        $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($exception->getMessage()));
        $html .= sprintf('<div><strong>File:</strong> %s</div>', $exception->getFile());
        $html .= sprintf('<div><strong>Line:</strong> %s</div>', $exception->getLine());
        $html .= '<h2>Trace</h2>';

        return $html . sprintf('<pre>%s</pre>', htmlentities($exception->getTraceAsString()));
    }
    
    public function setDefaultErrorTitle(string $text): self {
        
        $this->defaultErrorTitle = $text;

        return $this;
    }

    public function setDefaultErrorDescription(string $text): self {
        
        $this->defaultErrorDescription = $text;

        return $this;
    }
}
