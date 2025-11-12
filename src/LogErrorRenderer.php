<?php
declare(strict_types=1);

namespace SlimMvcTools;

use \SlimMvcTools\ContainerKeys;
use \SlimMvcTools\AppSettingsKeys;
use \Psr\Container\ContainerInterface;

/**
 * Description of LogErrorRenderer
 *
 * @author rotimi
 * @psalm-suppress UnusedClass
 */
class LogErrorRenderer extends \Slim\Error\Renderers\PlainTextErrorRenderer {

    use BaseErrorRendererTrait;
    
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string {
        
        $nl = PHP_EOL;
        $logErrorDetails = false;
        $text = "{$this->getErrorTitle($exception)}{$nl}";

        /** @psalm-suppress MixedAssignment */
        $appSettings = (array)$this->getContainerItem(ContainerKeys::APP_SETTINGS, []);
        
        if( \array_key_exists(AppSettingsKeys::LOG_ERROR_DETAILS, $appSettings) ) {

            $logErrorDetails = (bool)$appSettings[AppSettingsKeys::LOG_ERROR_DETAILS];
        }
        
        if($exception instanceof \Slim\Exception\HttpException) {
            
            $text .= sprintf('Request Uri: %s ', $exception->getRequest()->getUri()->__toString() . $nl);
        }

        if ($displayErrorDetails || $logErrorDetails) {
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
        $text = sprintf("Type: %s{$nl}", $exception::class);
        $code = $exception->getCode();
        $text .= sprintf("Code: %s{$nl}", $code);
        $text .= sprintf("Message: %s{$nl}", $exception->getMessage());
        $text .= sprintf("File: %s{$nl}", $exception->getFile());
        $text .= sprintf("Line: %s{$nl}", $exception->getLine());

        return $text .sprintf("Trace: %s{$nl}", $exception->getTraceAsString());
    }
}
