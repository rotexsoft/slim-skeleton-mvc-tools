<?php
declare(strict_types=1);

namespace SlimMvcTools;

/**
 * Description of LogErrorRenderer
 *
 * @author rotimi
 * @psalm-suppress UnusedClass
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
    
    private function formatExceptionFragment(\Throwable $exception): string {
        
        $nl = PHP_EOL;
        $text = sprintf("Type: %s{$nl}", get_class($exception));
        $code = $exception->getCode();
        $text .= sprintf("Code: %s{$nl}", $code);
        $text .= sprintf("Message: %s{$nl}", htmlentities($exception->getMessage()));
        $text .= sprintf("File: %s{$nl}", $exception->getFile());
        $text .= sprintf("Line: %s{$nl}", $exception->getLine());

        return $text .sprintf("Trace: %s{$nl}", $exception->getTraceAsString());
    }
}
