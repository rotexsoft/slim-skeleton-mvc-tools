<?php
declare(strict_types=1);

namespace SlimMvcTools;

/**
 * Description of LogErrorRenderer
 *
 * @author rotimi
 */
class LogErrorRenderer extends \Slim\Error\Renderers\PlainTextErrorRenderer {

    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string {
        
        $nl = PHP_EOL;
        $text = "{$this->getErrorTitle($exception)}{$nl}";
        
        if($exception instanceof \Slim\Exception\HttpException) {
            
            $text .= sprintf('Request Uri: %s ', $exception->getRequest()->getUri()->__toString() . $nl);
        }

        if ($displayErrorDetails) {
            $text .= $this->formatExceptionFragment($exception);

            while ($exception = $exception->getPrevious()) {
                $text .= "{$nl}Previous Error:{$nl}";
                $text .= $this->formatExceptionFragment($exception);
            }
        }

        return $text;
    }
    
}
