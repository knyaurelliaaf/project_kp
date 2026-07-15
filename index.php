<?php
session_start();

define('BASE_URL', '/project_kp');

require_once 'core/Helper.php';
require_once 'core/Controller.php';
require_once 'core/Model.php';

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';

// Kalau akses  (/) dan belum login -> redirect ke login
if (empty($url) && !isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/auth/login');
    exit;
}

// Kalau akses (/) dan sudah login -> redirect ke dashboard
if (empty($url) && isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

$controllerName = !empty($url[0]) ? ucfirst($url[0]) . 'Controller' : 'AuthController';
$method = isset($url[1]) ? $url[1] : 'index';
$params = array_slice($url, 2);

$controllerFile = 'app/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();
    
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        die("Method {$method} tidak ditemukan di {$controllerName}");
    }
} else {
    die("Controller {$controllerName} tidak ditemukan");
}