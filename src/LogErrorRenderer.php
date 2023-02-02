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
    
    private function formatExceptionFragment(Throwable $exception): string {
        
        $text = sprintf("Type: %s\n", get_class($exception));

        $code = $exception->getCode();
        /** @var int|string $code */
        $text .= sprintf("Code: %s\n", $code);

        $text .= sprintf("Message: %s\n", htmlentities($exception->getMessage()));

        $text .= sprintf("File: %s\n", $exception->getFile());

        $text .= sprintf("Line: %s\n", $exception->getLine());

        $text .= sprintf('Trace: %s', $exception->getTraceAsString());

        return $text;
    }
}
