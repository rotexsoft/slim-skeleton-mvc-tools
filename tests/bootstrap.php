<?php
error_reporting(E_ALL | E_STRICT);
session_start();

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor/autoload.php';

if(!defined('SMVC_APP_ROOT_PATH')){
    
    define('SMVC_APP_ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'fake-smvc-app-root' );
}

if(!defined('SMVC_APP_ENV_DEV')){
    
    define('SMVC_APP_ENV_DEV', 'dev');
}

if(!defined('SMVC_APP_ENV_STAGING')){
    
    define('SMVC_APP_ENV_STAGING', 'staging' );
}

if(!defined('SMVC_APP_ENV_TESTING')){
    
    define('SMVC_APP_ENV_TESTING', 'testing' );
}

if(!defined('SMVC_APP_ENV_PRODUCTION')){
    
    define('SMVC_APP_ENV_PRODUCTION', 'production' );
}

if(!function_exists('sMVC_GetCurrentAppEnvironment')) {
    
    function sMVC_GetCurrentAppEnvironment(): string {
        
        return sMVC_DoGetCurrentAppEnvironment(SMVC_APP_ROOT_PATH);
    }
}
