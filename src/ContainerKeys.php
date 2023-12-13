<?php

declare(strict_types=1);

namespace SlimMvcTools;

/**
 * A class with only constant definitions of keys for items put in the instance
 * of \SlimMvcTools\Container that gets injected into 
 * \SlimMvcTools\Controllers\BaseController or any of its sub-classes
 * 
 *
 * @author rotimi
 */
final class ContainerKeys {

    public const LAYOUT_RENDERER = 'new_layout_renderer';
    public const VIEW_RENDERER = 'new_view_renderer';
    public const LOGGER = 'logger';
    public const VESPULA_AUTH = 'vespula_auth';
    public const APP_SETTINGS = 'settings';
    public const NAMESPACES_4_CONTROLLERS = 'namespaces_for_controllers';
}