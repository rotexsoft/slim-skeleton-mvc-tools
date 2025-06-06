<?php
namespace SlimMvcTools;

/**
 *
 * @author rotimi
 */
trait BaseErrorRendererTrait {
    
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
