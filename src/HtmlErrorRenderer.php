<?php
declare(strict_types=1);

namespace SlimMvcTools;

/**
 * Description of HtmlErrorRenderer
 *
 * @author rotimi
 */
class HtmlErrorRenderer extends \Slim\Error\Renderers\HtmlErrorRenderer {
    
    protected string $full_path_to_error_template_file = '';
    
    public function __construct(string $full_path_to_error_template_file) {
        
        $this->full_path_to_error_template_file = $full_path_to_error_template_file;
    }
    
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string {
        
        $requestLine = '';
        
        if($exception instanceof \Slim\Exception\HttpException) {
            
            $requestLine .=  sprintf('<div><strong>Request Uri:</strong> %s</div><br>', $exception->getRequest()->getUri()->__toString());
        }
        
        if ($displayErrorDetails) {
            
            $html = '<p>The application could not run because of the following error:</p>';
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
        
        if($this->full_path_to_error_template_file === '') {
            
            return parent::renderHtmlBody($title, $html);
        }
        
        $file_contents = 
            file_get_contents($this->full_path_to_error_template_file);
        
        return sprintf($file_contents, $title, $title, $html);
    }
}
