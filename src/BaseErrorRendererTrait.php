<?php
namespace SlimMvcTools;

use \SlimMvcTools\ContainerKeys,
    \Slim\Exception\HttpException;

/**
 *
 * @author rotimi
 */
trait BaseErrorRendererTrait {
    
    protected ?\Psr\Container\ContainerInterface $container = null;

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getContainer(): ?\Psr\Container\ContainerInterface {
        
        return $this->container;
    }

    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function setContainer(?\Psr\Container\ContainerInterface $newContainer): static {
        
        $this->container = $newContainer;
        
        return $this;
    }
    
    public function getLocalizedText(string $localeKey, string $fallbackText=''): string {
        
        if(
            $this->container instanceof \Psr\Container\ContainerInterface
            && $this->container->has(ContainerKeys::LOCALE_OBJ)
            && $this->container->get(ContainerKeys::LOCALE_OBJ) instanceof \Vespula\Locale\Locale
        ) {
            /**
             * @psalm-suppress MixedAssignment
             * @psalm-suppress MixedMethodCall
             */
            $localizedText = $this->container->get(ContainerKeys::LOCALE_OBJ)->gettext($localeKey);
            
            // ($localizedText === $localeKey) when $localeKey is not found in the locale object
            return ($localizedText === $localeKey) ? $fallbackText : $localizedText;
            
        }
            
        return $fallbackText;
    }
    
    protected function getErrorTitle(\Throwable $exception): string {

        if ($exception instanceof HttpException) {

            return $this->getLocalizedText($exception::class .'_title', $exception->getTitle());
        }

        return $this->defaultErrorTitle;
    }

    protected function getErrorDescription(\Throwable $exception): string {

        if ($exception instanceof HttpException) {

            return $this->getLocalizedText($exception::class .'_description', $exception->getDescription());
        }

        return $this->defaultErrorDescription;
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function setDefaultErrorTitle(string $text): static {
        
        $this->defaultErrorTitle = $text;

        return $this;
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function setDefaultErrorDescription(string $text): static {
        
        $this->defaultErrorDescription = $text;

        return $this;
    }
}
