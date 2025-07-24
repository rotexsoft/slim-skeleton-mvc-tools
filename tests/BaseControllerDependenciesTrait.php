<?php
declare(strict_types=1);

use \SlimMvcTools\Container,
    \SlimMvcTools\ContainerKeys,
    \SlimMvcTools\AppSettingsKeys,
    \SlimMvcTools\Controllers\BaseController;

/**
 *
 * @author rotimi
 */
trait BaseControllerDependenciesTrait {
    
    protected function newResponse(string $body_text='Hello world'): \Psr\Http\Message\ResponseInterface {
        
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $responseBody = $psr17Factory->createStream($body_text);
        $response = $psr17Factory->createResponse(200)->withBody($responseBody);
        
        return $response;
    }
    
    protected function newRequest(string $url='http://tnyholm.se/blah?var=1'): \Psr\Http\Message\ServerRequestInterface {
        
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $request = $psr17Factory->createServerRequest('GET', $url);
        
        return $request;
    }
    
    protected function newVespulaAuth(string $alternate_auth_class=''): \Vespula\Auth\Auth {

        $pdo = new \PDO(
                    'sqlite::memory:', 
                    null, 
                    null, 
                    [
                        PDO::ATTR_PERSISTENT => true, 
                        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
                    ]
                );
        $pass1 = password_hash('admin' , PASSWORD_DEFAULT);
        $pass2 = password_hash('root' , PASSWORD_DEFAULT);

        $sql = <<<SQL
DROP TABLE IF EXISTS "user_authentication_accounts";
CREATE TABLE user_authentication_accounts (
    username VARCHAR(255), password VARCHAR(255)
);
INSERT INTO "user_authentication_accounts" VALUES( 'admin', '$pass1' );
INSERT INTO "user_authentication_accounts" VALUES( 'root', '$pass2' );
SQL;
        $pdo->exec($sql); //add two default user accounts

        //Optionally pass a maximum idle time and a time until the session 
        //expires (in seconds)
        $expire = 3600;
        $max_idle = 1200;
        $session = new \Vespula\Auth\Session\Session($max_idle, $expire);

        $cols = ['username', 'password'];
        $from = 'user_authentication_accounts';
        $where = ''; //optional
        $adapter = new \Vespula\Auth\Adapter\Sql($pdo, $from, $cols, $where);

        return ($alternate_auth_class === '')
                ? new \Vespula\Auth\Auth($adapter, $session)
                : new $alternate_auth_class($adapter, $session);
    }
    
    protected function getContainer(array $override_settings=[]): \Psr\Container\ContainerInterface {
        
        static $psr11Container;
        
        if(!$psr11Container || $override_settings !== []) {
            
            $psr11Container = new Container();
            
            $settings = [
                AppSettingsKeys::DISPLAY_ERROR_DETAILS => false,
                AppSettingsKeys::LOG_ERRORS => false,
                AppSettingsKeys::LOG_ERROR_DETAILS => false,
                AppSettingsKeys::ADD_CONTENT_LENGTH_HEADER => true,

                AppSettingsKeys::APP_BASE_PATH => '/da-path',
                AppSettingsKeys::ERROR_TEMPLATE_FILE_PATH => SMVC_APP_ROOT_PATH 
                                                            . DIRECTORY_SEPARATOR . 'src' 
                                                            . DIRECTORY_SEPARATOR . 'layout-templates' 
                                                            . DIRECTORY_SEPARATOR .'error-template.html',
                AppSettingsKeys::USE_MVC_ROUTES => true,
                AppSettingsKeys::MVC_ROUTES_HTTP_METHODS => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                AppSettingsKeys::AUTO_PREPEND_ACTION_TO_ACTION_METHOD_NAMES => false,
                AppSettingsKeys::DEFAULT_CONTROLLER_CLASS_NAME => \SlimMvcTools\Controllers\BaseController::class,
                AppSettingsKeys::DEFAULT_ACTION_NAME => 'actionIndex',

                AppSettingsKeys::ERROR_HANDLER_CLASS => \SlimMvcTools\ErrorHandler::class,

                AppSettingsKeys::HTML_RENDERER_CLASS => \SlimMvcTools\HtmlErrorRenderer::class,
                AppSettingsKeys::JSON_RENDERER_CLASS => \SlimMvcTools\JsonErrorRenderer::class,
                AppSettingsKeys::LOG_RENDERER_CLASS  => \SlimMvcTools\LogErrorRenderer::class,
                AppSettingsKeys::XML_RENDERER_CLASS  => \SlimMvcTools\XmlErrorRenderer::class,
                
                AppSettingsKeys::SESSION_START_OPTIONS => [],
            ];
            
            foreach ($override_settings as $key => $value) {
                
                $settings[$key] = $value;
            }
            
            $psr11Container[ContainerKeys::APP_SETTINGS] = $settings;
                
            $psr11Container[ContainerKeys::DEFAULT_LOCALE] = 'en_US';
            $psr11Container[ContainerKeys::VALID_LOCALES] = ['en_US', 'fr_CA']; // add more values for languages you will be supporting in your application
            $psr11Container[ContainerKeys::LOCALE_OBJ] = function ($c) {

                $ds = DIRECTORY_SEPARATOR;
                $locale_obj = new \Vespula\Locale\Locale($c[ContainerKeys::DEFAULT_LOCALE]);
                $path_2_locale_language_files = __DIR__ . DIRECTORY_SEPARATOR . 'fake-smvc-app-root' . $ds.'config'.$ds.'languages';        
                $locale_obj->load($path_2_locale_language_files); //load local entries for base controller
    
                if(session_status() !== \PHP_SESSION_ACTIVE) {

                    // Try to start or resume existing session

                    $sessionOptions = $c->get(ContainerKeys::APP_SETTINGS)[AppSettingsKeys::SESSION_START_OPTIONS];

                    if(isset($sessionOptions['name'])) {

                        ////////////////////////////////////////////////////////////////
                        // Set the session name first
                        // https://www.php.net/manual/en/function.session-start.php
                        //      To use a named session, call session_name() before 
                        //      calling session_start(). 
                        // 
                        // https://www.php.net/manual/en/function.session-name.php
                        ////////////////////////////////////////////////////////////////
                        session_name($sessionOptions['name']);
                    }

                    session_start($sessionOptions);
                }
                
                // Try to update to previously selected language if stored in session
                if (
                    session_status() === PHP_SESSION_ACTIVE
                    && array_key_exists(BaseController::SESSN_PARAM_CURRENT_LOCALE_LANG, $_SESSION)
                ) {
                    $locale_obj->setCode($_SESSION[BaseController::SESSN_PARAM_CURRENT_LOCALE_LANG]);
                }
    
                return $locale_obj;
            };
            
            $psr11Container[ContainerKeys::NAMESPACES_4_CONTROLLERS] = [
                '\\SlimMvcTools\\Controllers\\',
                '\\SMVCTools\\Tests\\TestObjects\\',
            ];
            $psr11Container[ContainerKeys::VESPULA_AUTH] = fn() => $this->newVespulaAuth();
            $psr11Container[ContainerKeys::LOGGER] = fn() => new \SMVCTools\Tests\TestObjects\InMemoryLogger();

            //Object for rendering layout files
            $psr11Container[ContainerKeys::LAYOUT_RENDERER] = 
                $psr11Container->factory(function ($c) {

                    // return a new instance on each access to 
                    // $psr11Container[ContainerKeys::LAYOUT_RENDERER]
                    $ds = DIRECTORY_SEPARATOR;
                    $path_2_layout_files = __DIR__ . DIRECTORY_SEPARATOR . 'test-template-output';
                    $layout_renderer = new \Rotexsoft\FileRenderer\Renderer('', [], [$path_2_layout_files]);
                    $layout_renderer->setVar('__localeObj', $c[ContainerKeys::LOCALE_OBJ]);

                    return $layout_renderer;
                });

            //Object for rendering view files
            $psr11Container[ContainerKeys::VIEW_RENDERER] = 
                $psr11Container->factory(function ($c) {

                    // Return a new instance on each access to 
                    // $psr11Container[ContainerKeys::VIEW_RENDERER]
                    $ds = DIRECTORY_SEPARATOR;
                    $path_2_view_files = __DIR__ . DIRECTORY_SEPARATOR . 'fake-smvc-app-root' ."{$ds}src{$ds}views{$ds}base";
                    $view_renderer = new \Rotexsoft\FileRenderer\Renderer('', [], [$path_2_view_files]);
                    $view_renderer->setVar('__localeObj', $c[ContainerKeys::LOCALE_OBJ]);

                    return $view_renderer;
                });
        } // if(!$psr11Container || $override_settings !== [])
        
        return $psr11Container;
    } // protected function getContainer(array $override_settings=[]): \Psr\Container\ContainerInterface
}
