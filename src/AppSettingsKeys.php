<?php

declare(strict_types=1);

namespace SlimMvcTools;

/**
 * Contains key names for your application's settings
 *
 * @author rotimi
 */
class AppSettingsKeys {
    
    public const DISPLAY_ERROR_DETAILS = 'displayErrorDetails';
    public const LOG_ERRORS = 'logErrors';
    public const LOG_ERROR_DETAILS = 'logErrorDetails';
    public const ADD_CONTENT_LENGTH_HEADER = 'addContentLengthHeader';
    public const APP_BASE_PATH = 'app_base_path';
    public const ERROR_TEMPLATE_FILE_PATH = 'error_template_file';
    public const USE_MVC_ROUTES = 'use_mvc_routes';
    public const MVC_ROUTES_HTTP_METHODS = 'mvc_routes_http_methods';
    public const AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES = 'auto_prepend_action_to_action_method_names';
    public const DEFAULT_CONTROLLER_CLASS_NAME = 'default_controller_class_name';
    public const DEFAULT_ACTION_NAME = 'default_action_name';
    public const ERROR_HANDLER_CLASS = 'error_handler_class';
    public const HTML_RENDERER_CLASS = 'html_renderer_class';
    public const JSON_RENDERER_CLASS = 'json_renderer_class';
    public const LOG_RENDERER_CLASS = 'log_renderer_class';
    public const XML_RENDERER_CLASS = 'xml_renderer_class';
    public const SESSION_START_OPTIONS = 'session_start_options';
    
}
