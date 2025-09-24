<?php
// Chargement de la config
$config = require __DIR__ . '/../Config/config.php';



// Définition de la constante BASE_URL (si pas déjà définie)
if (!defined('BASE_URL')) {
    define('BASE_URL', $config['base_url']);
}
/* Autoload des classes PHP (models et controllers) */
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../Config/',
        __DIR__ . '/../App/Controllers/',
        __DIR__ . '/../App/Models/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) require $file;
    }
});

/* On ouvre la session dès l'accès au site */
session_start();

var_dump(BASE_URL);
exit;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion DB
$db = new Database();
$pdo = $db->getConnection();

// Auth
$auth = new AuthController($pdo);

// Définition des routes
$routes = [
    'home' => [
        'view'   => __DIR__ . '/../App/Views/home.php',
        'public' => true,
        'data'   => function ($pdo) {
            $newsController = new NewsController($pdo);
            return [
                'news' => $newsController->getLatestNews(5)
            ];
        }
    ],

    // RDV
    'rdv'      => ['controller' => 'RDVController', 'method' => 'listRDV', 'role' => 'SECRETAIRE'],

    // Users (admin only)
    'users'    => ['controller' => 'UserController', 'method' => 'listUsers', 'role' => 'ADMIN'],

    // Services
    'services' => ['controller' => 'ServiceController', 'method' => 'listServices', 'role' => 'SECRETAIRE'],

    // News
    'news'              => ['controller' => 'NewsController', 'method' => 'list', 'public' => true],
    'news_show'         => ['controller' => 'NewsController', 'method' => 'show', 'public' => true],
    'create-news'       => ['controller' => 'NewsController', 'method' => 'create', 'role' => 'SECRETAIRE'],
    'create-news-valid' => ['controller' => 'NewsController', 'method' => 'createValid', 'role' => 'SECRETAIRE'],
    'edit-news'         => ['controller' => 'NewsController', 'method' => 'editForm', 'role' => 'ADMIN'],
    'update-news'       => ['controller' => 'NewsController', 'method' => 'update', 'role' => 'SECRETAIRE'],
    'delete-news'       => ['controller' => 'NewsController', 'method' => 'delete', 'role' => 'SECRETAIRE'],


    // Auth
    // 'login'  => ['view' => __DIR__ . '/../App/Views/users/login.php', 'public' => true],
    'login' => ['controller' => 'AuthController', 'method' => 'login', 'public' => true],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    // 'register' => ['view' => __DIR__ . '/../App/Views/users/register.php', 'public' => true],
    'register' => ['controller' => 'AuthController', 'method' => 'register', 'public' => true],
    'register-valid' => ['controller' => 'AuthController', 'method' => 'register', 'public' => true],
];

// Page demandée
$page = $_GET['page'] ?? 'home';

// Route inconnue -> 404
if (!isset($routes[$page])) {
    include __DIR__ . '/../App/Views/404.php';
    exit;
}

$route = $routes[$page];

// Vérification si accès public ou privé
$isPublic = $route['public'] ?? false;
if (!$isPublic && !$auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
}

// Vérification du rôle si nécessaire (avant d’appeler le contrôleur)
if (isset($route['role'])) {
    $auth->requireRole($route['role']);
}

// Si c’est une simple vue
if (isset($route['view'])) {
    $data = [];

    if (isset($route['data']) && is_callable($route['data'])) {
        $data = $route['data']($pdo);
    }

    extract($data);
    include $route['view'];
    exit;
}



// Sinon on appelle le contrôleur dynamiquement
$controllerName = $route['controller'];
$method         = $route['method'];

$controller = new $controllerName($pdo);

// Cas spécial RDV -> besoin de l’id user
if ($method === 'listRDV') {
    $controller->$method($_SESSION['user']->getId());
} else {
    $controller->$method();
}
