<?php
// fixing autoload register
spl_autoload_register("myAutoload");

function myAutoload ($class)
{
    $class= str_replace('\\', '/', $class);
    $classFile= "./{$class}.php";
    if (file_exists($classFile)) {
        require_once $classFile;
    } else {
        $lastDash= strrpos($classFile, '/');
        $classFile= substr($classFile, 0 , $lastDash).'.php';
        if (file_exists($classFile)) {
            require_once $classFile;
        }
    }
}

// debug 
function debug($data) 
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
}