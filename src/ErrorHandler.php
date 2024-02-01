<?php
declare(strict_types=1);

namespace SlimMvcTools;

/**
 * Description of ErrorHandler
 *
 * @author rotimi
 * @psalm-suppress UnusedClass
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ErrorHandler extends \Slim\Handlers\ErrorHandler {

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
    public function setContainer(\Psr\Container\ContainerInterface $container): self {

        $this->container = $container;
        return $this;
    }
}
