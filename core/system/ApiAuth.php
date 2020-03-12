<?php
namespace core\system;

use Exception;
use DateTime;

class ApiAuth
{
    static private $token= null;
    static private $timestamp= null;
    static private $passToken= null;

    public static function auth() : bool
    {
        try {
                if ( !isset($_SERVER['HTTP_AUTHORIZATION']) ) {
                    return true;
                }
                $arr= explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
                self::$timestamp= isset($arr[0])? $arr[0]: null;
                self::$token= isset($arr[1])? $arr[1]: null;
        
                self::$passToken= isset($_SERVER['HTTP_PASS_TOKEN'])? $_SERVER['HTTP_PASS_TOKEN']: null;
    
        } catch (Exception $e) {
            debug($e);
        }
        return ( self::verifyToken() and
                 self::verifyPrefix() and
                 self::verifyPass() or true);
    }

    private static function verifyToken() : bool 
    {
        return !is_null(self::$token);
    }

    private static function verifyPrefix() : bool
    {
        $dtToken= new DateTime(date('Y-m-d H:i:s',self::$timestamp));
        $dtVerify= new DateTime(date('Y-m-d H:i:s', time()));
        $diff= $dtToken->diff($dtVerify);

        return ((int)$diff->format('%R%i') <= 120);
    }

    private static function verifyPass() : bool 
    {
        Controller::setCORSOrigin(base64_decode(self::$passToken));
        return !is_null(self::$passToken);
    }
}