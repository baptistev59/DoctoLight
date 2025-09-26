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

// Génération du CSRF token si pas encore défini
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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


    // Users
    'users'          => ['controller' => 'UserController', 'method' => 'listUsers', 'role' => 'ADMIN'],
    'users_create'   => ['controller' => 'UserController', 'method' => 'create', 'role' => 'ADMIN'],
    'users_edit'     => ['controller' => 'UserController', 'method' => 'edit'],
    'users_delete'   => ['controller' => 'UserController', 'method' => 'delete', 'role' => 'ADMIN'],
    'users_view'     => ['controller' => 'UserController', 'method' => 'view'],
    'users_toggle'   => ['controller' => 'UserController', 'method' => 'toggleActive', 'role' => 'ADMIN'],


    // Services
    'services' => ['controller' => 'ServiceController', 'method' => 'listServices', 'role' => 'SECRETAIRE'],

    // News
    'news'              => ['controller' => 'NewsController', 'method' => 'list', 'public' => true],
    'news_show'         => ['controller' => 'NewsController', 'method' => 'show', 'public' => true],
    'create-news'       => ['controller' => 'NewsController', 'method' => 'create', 'role' => 'SECRETAIRE'],
    'create-news-valid' => ['controller' => 'NewsController', 'method' => 'createValid', 'role' => 'SECRETAIRE'],
    'edit-news'         => ['controller' => 'NewsController', 'method' => 'editForm', 'role' => 'SECRETAIRE'],
    'update-news'       => ['controller' => 'NewsController', 'method' => 'update', 'role' => 'SECRETAIRE'],
    'delete-news'       => ['controller' => 'NewsController', 'method' => 'delete', 'role' => 'SECRETAIRE'],


    // Auth

    'login' => ['controller' => 'AuthController', 'method' => 'login', 'public' => true],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout', 'public' => true],

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

// Gestion des routes via le tableau de routes
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

// Sinon on appelle le contrôleur dynamique
if (isset($route['controller']) && isset($route['method'])) {
    $controllerName = $route['controller'];
    $method         = $route['method'];

    // Passe PDO + AuthController au constructeur
    $controller = new $controllerName($pdo, $config);

    // Cas spécial pour les méthodes nécessitant un ID
    if (in_array($method, ['edit', 'delete', 'view', 'toggleActive'])) {
        $id = $_GET['id'] ?? null;
        if ($id !== null) {
            $controller->$method((int)$id);
            exit;
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo "ID manquant pour la méthode $method.";
            exit;
        }
    }

    // Cas spécial listRDV -> besoin de l’ID utilisateur
    if ($method === 'listRDV') {
        $controller->$method($_SESSION['user']->getId());
        exit;
    }

    // Méthode standard sans paramètre
    $controller->$method();
    exit;
}
