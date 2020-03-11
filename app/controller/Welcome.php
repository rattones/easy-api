<?php
namespace app\controller;

use core\system\Controller;

class Welcome extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        self::response(200, ['message'=>'Welcome to the Easy-API']);
    }
}