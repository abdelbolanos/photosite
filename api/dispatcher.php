<?php
use \Router\Router as Router;

include 'constants.php';

spl_autoload_register(function ($class) {
	$file_path = str_replace("\\", '/', $class) . '.php';
    //echo "Include: " . $file_path . PHP_EOL; 
    include $file_path;
});


$router = new Router($_GET['action']);
$router->processRoute();
