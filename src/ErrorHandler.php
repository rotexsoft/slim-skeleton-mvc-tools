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

//    public function __invoke(ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): \Psr\Http\Message\ResponseInterface {
//        
//        if($this->container !== null) {
//            
//            // In sub-classes of this class
//            // do some stuff with the container 
//            // like pull out a mailer object and
//            // send out notification emails about
//            // the current error before finally
//            // displaying the error page.
//        }
//        
//        return parent::__invoke($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails);
//    }
}
