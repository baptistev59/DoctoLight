<?php
// Initialisation de tout le projet

// Chargement config
/** @var array $config */
$config = require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/helpers.php';

// Définition BASE_URL
if (!defined('BASE_URL')) {
    define('BASE_URL', $config['base_url']);
}

// Autoload
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../Config/',
        __DIR__ . '/../App/Controllers/',
        __DIR__ . '/../App/Models/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion DB
$db  = new Database();
$pdo = $db->getConnection();

// Auth (injecté partout via $auth)
$auth = new AuthController($pdo);
