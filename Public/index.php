<?php
require_once __DIR__ . '/init.php'; // init.php charge config, helpers, DB, auth

/** @var array $config */


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


// Erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion DB
$db = new Database();
$pdo = $db->getConnection();

// Auth
$auth = new AuthController($pdo);

// Routes
$routes = [
    'home' => [
        'view' => 'home',
        'public' => true,
        'data' => fn($pdo) => ['news' => (new NewsController($pdo, $config))->getLatestNews(5)]
    ],
    // page de test
    'test_view' => [
        'view' => 'test',
        'public' => true,
        'data' => fn($pdo) => ['message' => 'Hello World!']
    ],

    // page de redirection
    'test-redirect' => [
        'controller' => 'AuthController',
        'method' => 'login',
        'public' => true
    ],

    // RDV
    'rdv' => ['controller' => 'RDVController', 'method' => 'listRDV'],
    'create_rdv' => ['controller' => 'RDVController', 'method' => 'create'],
    'create_rdv_valid' => ['controller' => 'RDVController', 'method' => 'createValid'],
    'rdv_store' => ['controller' => 'RDVController', 'method' => 'store', 'role' => ['PATIENT', 'SECRETAIRE']],


    // Users
    'users' => ['controller' => 'UserController', 'method' => 'listUsers', 'role' => 'ADMIN'],
    'users_create' => ['controller' => 'UserController', 'method' => 'create', 'role' => 'ADMIN'],
    'users_edit' => ['controller' => 'UserController', 'method' => 'edit'],
    'users_delete' => ['controller' => 'UserController', 'method' => 'delete', 'role' => 'ADMIN'],
    'users_view' => ['controller' => 'UserController', 'method' => 'view'],
    'users_toggle' => ['controller' => 'UserController', 'method' => 'toggleActive', 'role' => 'ADMIN'],

    'profile' => ['controller' => 'UserController', 'method' => 'profile'],

    // Services
    'services' => ['controller' => 'ServiceController', 'method' => 'listServices', 'role' => 'SECRETAIRE'],

    // News
    'news' => ['controller' => 'NewsController', 'method' => 'list', 'public' => true],
    'news_show' => ['controller' => 'NewsController', 'method' => 'show', 'public' => true],
    'create-news' => ['controller' => 'NewsController', 'method' => 'create', 'role' => 'SECRETAIRE'],
    'create-news-valid' => ['controller' => 'NewsController', 'method' => 'createValid', 'role' => 'SECRETAIRE'],
    'edit-news' => ['controller' => 'NewsController', 'method' => 'editForm', 'role' => 'SECRETAIRE'],
    'update-news' => ['controller' => 'NewsController', 'method' => 'update', 'role' => 'SECRETAIRE'],
    'delete-news' => ['controller' => 'NewsController', 'method' => 'delete', 'role' => 'SECRETAIRE'],

    // Auth
    'login' => ['controller' => 'AuthController', 'method' => 'login', 'public' => true],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout', 'public' => true],
    'register' => ['controller' => 'AuthController', 'method' => 'register', 'public' => true],
    'register-valid' => ['controller' => 'AuthController', 'method' => 'register', 'public' => true],
];

// Page demandée
$page = $_GET['page'] ?? 'home';
if (!isset($routes[$page])) {
    view('404');
    exit;
}

$route = $routes[$page];

// Vérification accès
$isPublic = $route['public'] ?? false;
if (!$isPublic && !$auth->isLoggedIn()) {
    redirect(BASE_URL . 'index.php?page=login');
}

// Vérification rôle
if (isset($route['role'])) {
    $auth->requireRole($route['role']);
}

// Simple vue
if (isset($route['view'])) {
    $data = isset($route['data']) && is_callable($route['data']) ? $route['data']($pdo) : [];
    view($route['view'], $data);
    exit;
}

// Contrôleur dynamique
if (isset($route['controller'], $route['method'])) {
    $controllerName = $route['controller'];
    $method = $route['method'];

    $controller = new $controllerName($pdo, $config);

    // Méthodes nécessitant ID
    if (in_array($method, ['edit', 'delete', 'view', 'toggleActive'])) {
        $id = $_GET['id'] ?? null;
        if ($id !== null) {
            $controller->$method((int)$id);
            exit;
        }
        header("HTTP/1.0 400 Bad Request");
        echo "ID manquant pour la méthode $method.";
        exit;
    }

    // Cas listRDV
    if ($method === 'listRDV') {
        $controller->$method($_SESSION['user']->getId());
        exit;
    }

    // Méthode standard
    $controller->$method();
    exit;
}
