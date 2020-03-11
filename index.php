<?php
require_once '__autoload.php';

define('APP_PATH', __DIR__);

use core\db\PDOConnection;
use core\system\ApiAuth;
use core\system\Controller;
use core\system\Router;

class App 
{
    public $db; 
    
    public function __construct()
    {
        require_once('app/config.php');
        $this->routes= $routes;
        $this->db= new PDOConnection();
    }

    public function run() 
    {
        if ( ApiAuth::auth() ) {
            Router::init($this->routes);
        } else {
            Controller::response(400);
        }
        // \debug($this->db);
    }
}

$app= new App();
$app->run();