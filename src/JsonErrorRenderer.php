<?php
declare(strict_types=1);

namespace SlimMvcTools;

/**
 * Description of JsonErrorRenderer
 *
 * @author rotimi
 */
class JsonErrorRenderer extends \Slim\Error\Renderers\JsonErrorRenderer {
    
    use BaseErrorRendererTrait;
}
