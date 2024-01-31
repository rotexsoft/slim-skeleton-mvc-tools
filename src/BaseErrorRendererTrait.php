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
    public function setDefaultErrorTitle(string $text): self {
        
        $this->defaultErrorTitle = $text;

        return $this;
    }
    
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function setDefaultErrorDescription(string $text): self {
        
        $this->defaultErrorDescription = $text;

        return $this;
    }
}
