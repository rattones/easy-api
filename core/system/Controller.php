<?php
namespace core\system;

use core\system\Request;

class Controller extends Request
{

    static private $status= [
        '100' => '100 Continue',
        '101' => '101 Switching Protocols',
        '103' => '103 Early Hints',
        '200' => '200 OK',
        '201' => '201 Created',
        '202' => '202 Accepted',
        '203' => '203 Non-Authoritative Information',
        '204' => '204 No Content',
        '205' => '205 Reset Content',
        '206' => '206 Partial Content',
        '300' => '300 Multiple Choices',
        '301' => '301 Moved Permanently',
        '302' => '302 Found',
        '303' => '303 See Other',
        '304' => '304 Not Modified',
        '307' => '307 Redirecionamento temporário',
        '308' => '308 Permanent Redirect',
        '400' => '400 Bad Request',
        '401' => '401 Unauthorized',
        '402' => '402 Payment Required',
        '403' => '403 Forbidden',
        '404' => '404 Not Found',
        '405' => '405 Method Not Allowed',
        '406' => '406 Not Acceptable',
        '407' => '407 Proxy Authentication Required',
        '408' => '408 Request Timeout',
        '409' => '409 Conflict',
        '410' => '410 Gone',
        '411' => '411 Length Required',
        '412' => '412 Precondition Failed',
        '413' => '413 Payload Too Large',
        '414' => '414 URI Too Long',
        '415' => '415 Unsupported Media Type',
        '416' => '416 Range Not Satisfiable',
        '417' => '417 Expectation Failed',
        '418' => '418 I\'m a teapot',
        '422' => '422 Unprocessable Entity',
        '425' => '425 Too Early',
        '426' => '426 Upgrade Required',
        '428' => '428 Precondition Required',
        '429' => '429 Too Many Requests',
        '431' => '431 Request Header Fields Too Large',
        '451' => '451 Unavailable For Legal Reasons',
        '500' => '500 Internal Server Error',
        '501' => '501 Not Implemented',
        '502' => '502 Bad Gateway',
        '503' => '503 Service Unavailable',
        '504' => '504 Gateway Timeout',
        '505' => '505 HTTP Version Not Supported',
        '506' => '506 Variant Also Negotiates',
        '507' => '507 Insufficient Storage',
        '508' => '508 Loop Detected',
        '510' => '510 Not Extended',
        '511' => '511 Network Authentication Required'
    ];

    private static $callback= null;

    private static $CORSOrigin= null;

    public function __construct()
    {
        // @TODO need?
    }
   
    public static function response(int $status, array $data= null)
    {
        // for apache with .htaccess file
        // if (!headers_sent()) {
        //     http_response_code($status);
        // }
        header("HTML/1.1 ".self::$status[$status], false, $status);
        header("Connection: keep-alive");   
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        header('Content-Type: application/json; charset=UTF-8');
        if (self::$CORSOrigin ?? false) {
            header("Access-Control-Allow-Headers: Authorization, PASS_TOKEN, X-Requested-With, accept", false);
            header("Access-Control-Allow-Methods: GET,PUT,POST,DELETE,HEAD,OPTIONS", false);
            header("Access-Control-Allow-Origin: ".self::$CORSOrigin, false);
            header('Access-Control-Max-Age: 1000');
            header("Vary: Accept-Encoding, Origin", false); // necessary whn origin is not '*'
        }
        if (!empty($data)) {
            if (self::$callback === "") {
                echo json_encode($data);
            } else {
                $callback= self::$callback ?? 'null';
                $jsonData= json_encode($data);
                echo "{$callback}({$jsonData})";
            }
        }
        // exiting application to avoid after controller response extra executions
        exit(0);
    }

    public static function preflight()
    {
        // die("aki");
        header("HTML/1.1 ".self::$status[100], false, 100);
        header("Connection: keep-alive");   
        // header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        // header('Content-Type: application/json; charset=UTF-8');
        if (self::$CORSOrigin ?? false) {
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Accept, Content-Type", false);
            header("Access-Control-Allow-Methods: GET,PUT,POST,DELETE,HEAD,OPTIONS", false);
            header("Access-Control-Allow-Origin: *", false); #.self::$CORSOrigin, false);
            header('Access-Control-Max-Age: 1000');
            header("Vary: Origin", false); // necessary whn origin is not '*'
        }

        exit(0);
    }

    public static function execute(string $class, string $method, array $params, string $callback= null)
    {
        $class= "app\controller\\{$class}";

        if (class_exists($class)) {
            $object= new $class();
            
            if (method_exists($object, $method)) {
                self::$callback= $callback;
                call_user_func_array([$object, $method], $params);                
            } else {
                self::response(404);
            }
        } else {
            self::response(404);
        }

    }

    public static function setCORSOrigin(string $passToken) 
    {
        self::$CORSOrigin= $passToken;
    }

    public static function debug($data) {
        $data= var_dump($data);
        self::response(200, ['debug'=>$data]);
    }
}