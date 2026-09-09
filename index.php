<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!defined('BASE_URL')) {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $scriptDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim(str_replace('\\', '/', $scriptDir), '/');
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
    define('BASE_URL', $scheme . '://' . $_SERVER['HTTP_HOST'] . $scriptDir);
}

require_once 'core/Helper.php';
require_once 'core/Controller.php';
require_once 'core/Model.php';

$rawUrl = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$rawUrl = filter_var($rawUrl, FILTER_SANITIZE_URL);
$urlParts = array_values(array_filter(explode('/', $rawUrl)));

// Jika segmen pertama adalah nama folder root ('project_kp'), abaikan segmen tersebut
if (!empty($urlParts) && strtolower($urlParts[0]) === 'project_kp') {
    array_shift($urlParts);
}

// Kalau akses root (/) dan belum login -> redirect ke login
if (empty($urlParts) && !isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/auth/login');
    exit;
}

// Kalau akses root (/) dan sudah login -> redirect sesuai role
if (empty($urlParts) && isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? '';
    if ($role === 'payroll') {
        header('Location: ' . BASE_URL . '/payroll');
        exit;
    } elseif (in_array($role, ['admin_gaji', 'payroll_wa'])) {
        header('Location: ' . BASE_URL . '/admin_gaji');
        exit;
    } else {
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }
}

$rawController = !empty($urlParts[0]) ? $urlParts[0] : 'Auth';
$controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $rawController))) . 'Controller';
$method = isset($urlParts[1]) ? $urlParts[1] : 'index';
$params = array_slice($urlParts, 2);

$controllerFile = 'app/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    $fallbackName = ucfirst($rawController) . 'Controller';
    $fallbackFile = 'app/controllers/' . $fallbackName . '.php';
    if (file_exists($fallbackFile)) {
        $controllerName = $fallbackName;
        $controllerFile = $fallbackFile;
    }
}

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