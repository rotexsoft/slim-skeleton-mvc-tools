<?php
declare(strict_types=1);

namespace SlimMvcTools;

use \Pimple\Container as PimpleContainer;
use \Psr\Container\ContainerInterface;

/**
 * Description of Container
 *
 * @author rotimi
 * @psalm-suppress UnusedClass
 * @psalm-suppress ClassMustBeFinal
 */
class Container extends PimpleContainer implements ContainerInterface {

    public function __construct(array $values = []) {
        
        parent::__construct($values);
    }

    public function get(string $id) {
        
        return $this->offsetGet($id);
    }

    public function has(string $id): bool {
        
        return $this->offsetExists($id);
    }
}
