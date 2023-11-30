<?php
declare(strict_types=1);

use \SlimMvcTools\Container;

/**
 *
 * @author rotimi
 */
trait BaseControllerDependenciesTrait {
    
    protected function newResponse(): \Psr\Http\Message\ResponseInterface {
        
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $responseBody = $psr17Factory->createStream('Hello world');
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
                'displayErrorDetails' => false,
                'logErrors' => false,
                'logErrorDetails' => false,
                'addContentLengthHeader' => true,

                'app_base_path' => '/da-path',
                'error_template_file'=> 'error-template.php',
                'use_mvc_routes' => true,
                'mvc_routes_http_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'auto_prepend_action_to_action_method_names' => false,
                'default_controller_class_name' => \SlimMvcTools\Controllers\BaseController::class,
                'default_action_name' => 'actionIndex',

                'error_handler_class' => \SlimMvcTools\ErrorHandler::class,
                'html_renderer_class' => \SlimMvcTools\HtmlErrorRenderer::class,
                'log_renderer_class'  => \SlimMvcTools\LogErrorRenderer::class,
                
                'base_controller_action_login_empty_username_msg' => "The 'username' field is empty.",
                'base_controller_action_login_empty_password_msg' => "The 'password' field is empty.",
                'base_controller_do_login_auth_is_valid_msg' => 'You are now logged into a new session.',
                'base_controller_do_login_auth_not_is_valid_msg' => 'Login Failed!',
                'base_controller_do_login_auth_v_auth_exception_general_msg' => 'Login Failed!<br>Please contact site administrator or try again later.',
                'base_controller_do_login_auth_v_auth_exception_back_end_msg' => "Login Failed!<br>Can't connect to login server right now.<br>Please try again later.",
                'base_controller_do_login_auth_v_auth_exception_user_passwd_msg' => 'Login Failed!<br>Incorrect User Name and Password combination.<br>Please try again.',
                'base_controller_do_login_auth_exception_msg' => 'Login Failed!<br>Please contact the site administrator.',
                'base_controller_action_login_status_is_anon_msg' => 'You are not logged in.',
                'base_controller_action_login_status_is_idle_msg' => 'Your session was idle for too long. Please log in again.',
                'base_controller_action_login_status_is_expired_msg' => 'Your session has expired. Please log in again.',
                'base_controller_action_login_status_is_valid_msg' => 'You are still logged in.',
                'base_controller_action_login_status_unknown_msg' => 'Unknown session status.',
            ];
            
            foreach ($override_settings as $key => $value) {
                
                $settings[$key] = $value;
            }
            
            $psr11Container['settings'] = $settings;
            
            $psr11Container['namespaces_for_controllers'] = [
                '\\SlimMvcTools\\Controllers\\',
                '\\SMVCTools\\Tests\\TestObjects\\',
            ];
            $psr11Container['vespula_auth'] = fn() => $this->newVespulaAuth();

            //Object for rendering layout files
            $psr11Container['new_layout_renderer'] = $psr11Container->factory(function () {

                //return a new instance on each access to $psr11Container['new_layout_renderer']
                $ds = DIRECTORY_SEPARATOR;
                $path_2_layout_files = __DIR__ . DIRECTORY_SEPARATOR . 'test-template-output';
                $layout_renderer = new \Rotexsoft\FileRenderer\Renderer('', [], [$path_2_layout_files]);

                return $layout_renderer;
            });

            //Object for rendering view files
            $psr11Container['new_view_renderer'] = $psr11Container->factory(function () {

                //return a new instance on each access to $psr11Container['new_view_renderer']
                $ds = DIRECTORY_SEPARATOR;
                $path_2_view_files = __DIR__ . DIRECTORY_SEPARATOR . 'fake-smvc-app-root' ."{$ds}src{$ds}views{$ds}base";
                $view_renderer = new \Rotexsoft\FileRenderer\Renderer('', [], [$path_2_view_files]);

                return $view_renderer;
            });
        }
        
        return $psr11Container;
    } // protected function getContainer(array $override_settings=[]): \Psr\Container\ContainerInterface
}
