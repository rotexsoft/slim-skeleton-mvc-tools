<?php
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
    
    public function renderHtmlBody(string $title = '', string $html = ''): string {
        
        if($this->full_path_to_error_template_file === '') {
            
            return parent::renderHtmlBody($title, $html);
        }
        
        $file_contents = 
            file_get_contents($this->full_path_to_error_template_file);
        
        return sprintf($file_contents, $title, $title, $html);
    }
}
