<?php
declare(strict_types=1);

namespace SlimMvcTools;

/**
 * A class with only constant definitions of keys for items put in the instance
 * of \SlimMvcTools\Container that gets injected into 
 * \SlimMvcTools\Controllers\BaseController or any of its sub-classes
 * 
 * @author rotimi
 */
class ContainerKeys {

    public const NEW_RESPONSE_OBJECT = 'new_response_object';
    public const NEW_REQUEST_OBJECT = 'new_request_object';
    public const LAYOUT_RENDERER = 'new_layout_renderer';
    public const VIEW_RENDERER = 'new_view_renderer';
    public const LOGGER = 'logger';
    public const VESPULA_AUTH = 'vespula_auth';
    public const APP_SETTINGS = 'settings';
    public const NAMESPACES_4_CONTROLLERS = 'namespaces_for_controllers';
    
    public const LOCALE_OBJ = 'vespula_locale_obj';
    public const VALID_LOCALES = 'valid_locales';
    public const DEFAULT_LOCALE = 'default_locale';
}
