<?php 
namespace core\system;

use core\system\Controller;

class Router 
{
    private static $method;
    private static $uri;
    private static $params= [];

    public static function init(array $routes)
    {
        self::$uri= explode('/', substr($_SERVER['REQUEST_URI'], 1));
        self::$method= strtolower($_SERVER['REQUEST_METHOD']);
        
        $action= self::findRoute($routes);
        if (empty($action)) {
            Controller::response(405);
        } else {
            self::executeRoute($action);
        }
        
    }
    
    private static function findRoute(array $routes) : string
    {
        foreach ( $routes[self::$method] as $route=>$action ) {
            $route= explode('/', $route);

            if ( $route[0] === self::$uri[0] and count($route) === count(self::$uri) ) {
                
                self::$params= [];
                for ( $i=0; $i<count($route); $i++ ) {
                    if ($route[$i] === self::$uri[$i]) {
                        // do nothing, just verifying route steps
                    } elseif ( substr($route[$i], 0, 1) === ':' ) {
                        self::$params[]= self::$uri[$i];
                    } else {
                        return "";
                    }
                }
                break;
            } else {
                $action= "";
            }
        }

        return $action;
    }

    private static function executeRoute(string $action)
    {
        $action= explode('/', $action);
        $class= $action[0];
        $method= (isset($action[1]))? $action[1]: 'index';

        Controller::execute($class, $method, self::$params);

    }


}