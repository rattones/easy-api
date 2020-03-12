<?php 
namespace core\system;

use core\system\Controller;

class Router 
{
    private static $method;
    private static $uri;
    private static $params= [];
    private static $callback= '';

    public static function init(array $routes)
    {
        self::$uri= explode('/', substr($_SERVER['REQUEST_URI'], 1));
        self::$method= strtolower($_SERVER['REQUEST_METHOD']);

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS' and
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'OPTIONS' and
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET') {
            Controller::preflight();
        }
        
        $action= self::findRoute($routes);
        if (empty($action)) {
            Controller::response(405, [$action, self::$uri]);
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
                $break= true;
                for ( $i=0; ($i<count($route) and $action != ""); $i++ ) {
                    
                    $param= self::calculateCallback(self::$uri[$i]);

                    if ($route[$i] === $param) {
                        // do nothing, just verifying route steps
                    } elseif ( substr($route[$i], 0, 1) === ':' ) {
                        self::$params[]= $param;
                    } else {
                        $break= false;
                    }

                }
                if ($break) {
                    break;
                }
            } else {
                $action= "";
            }
        }
    
        // Controller::debug([$route, self::$uri]);
        return $action;
    }

    private static function executeRoute(string $action)
    {
        $action= explode('/', $action);
        $class= $action[0];
        $method= (isset($action[1]))? $action[1]: 'index';

        Controller::execute($class, $method, self::$params, self::$callback);
    }

    private static function calculateCallback(string $stack) : string
    {
        $aux= explode('?', $stack);
        // \debug($aux);

        if (isset($aux[1])) {
            $uriParam= $aux[0];
            $params= explode('&', $aux[1]);

            foreach ($params as $p) {
                list($param, $value)= explode('=', $p);
                // $arrAux= explode('=', $p);
                // $param= (isset($arrAux[0]))? $arrAux[0]: null;
                // $value= (isset($arrAux[1]))? $arrAux[1]: null; // -> json data mal formatado
                if ($param == 'callback') {
                    self::$callback= $value;
                } else {
                    $get[$param]= $value; // @TODO need?
                }
            }
        } else {
            $uriParam= $stack;
        }

        return $uriParam;
    }

}