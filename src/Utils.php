<?php
declare(strict_types=1);

namespace SlimMvcTools;

use \Throwable;

/**
 * Description of Utils
 *
 * @author rotex
 */
class Utils {

    public static function getThrowableAsStr(Throwable $e, string $eol=PHP_EOL): string {

        $previous_throwable = $e; 
        $message = '';

        do {
            $message .= "Exception / Error Code: {$previous_throwable->getCode()}"
                . $eol . "Exception / Error Class: " . $previous_throwable::class
                . $eol . "File: {$previous_throwable->getFile()}"
                . $eol . "Line: {$previous_throwable->getLine()}"
                . $eol . "Message: {$previous_throwable->getMessage()}" . $eol
                . $eol . "Trace: {$eol}{$previous_throwable->getTraceAsString()}{$eol}{$eol}";
                
            $previous_throwable = $previous_throwable->getPrevious();
        } while( $previous_throwable instanceof Throwable );
        
        return $message;
    }
    
    /**
     * 
     * @param \Psr\Container\ContainerInterface $container a container from which the locale object will be retreived
     * @param SlimHttpExceptionClassNames $exception_class the exception class to instantiate
     * @param \Psr\Http\Message\RequestInterface $req a request object
     * @param string $err_message an error message for the exception, it is different from the localized description for the exception
     * @param \Throwable|null $previous_exception a previous exception if any
     * @psalm-suppress MixedArgument
     */
    public static function createSlimHttpExceptionWithLocalizedDescription(
        \Psr\Container\ContainerInterface $container,
        SlimHttpExceptionClassNames $exception_class,
        \Psr\Http\Message\RequestInterface $req,
        string $err_message,
        ?\Throwable $previous_exception = null
    ): \Slim\Exception\HttpSpecializedException {

            $exception_class_name = $exception_class->value;
            $exception =  new $exception_class_name($req, $err_message, $previous_exception);
            
            if(
                $container->has(ContainerKeys::LOCALE_OBJ)
                && $container->get(ContainerKeys::LOCALE_OBJ) instanceof \Vespula\Locale\Locale
            ) {
                /** @psalm-suppress MixedMethodCall */
                $exception->setDescription(
                    $container->get(ContainerKeys::LOCALE_OBJ)
                              ->gettext($exception_class->value.'_description')
                );
            }
            
            return $exception;
    }
}
