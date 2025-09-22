<?php
/* On ouvre la session dès l'accès au site */
session_start();

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

// Connexion DB
$db = new Database();
$pdo = $db->getConnection();

// Auth
$auth = new AuthController($pdo);

// Définition des routes avec l'indication du type d'accès (public, privée) et la différence entre les vues et les contrôlleurs
$routes = [
    'home' => [
        'view'  => __DIR__ . '/../App/Views/home.php',
        'public' => true,
        'data' => function ($pdo) {
            $newsController = new NewsController($pdo);
            return [
                'news' => $newsController->getLatestNews(5) // 5 dernières news
            ];
        }
    ],
    'rdv'               => ['controller' => 'RDVController', 'method' => 'listRDV'],
    'users'             => ['controller' => 'UserController', 'method' => 'listUsers'],
    'services'          => ['controller' => 'ServiceController', 'method' => 'listServices'],

    // News
    'news'              => ['controller' => 'NewsController', 'method' => 'list'],
    'news_show'         => ['controller' => 'NewsController', 'method' => 'show'],
    'create-news'       => ['controller' => 'NewsController', 'method' => 'create'],
    'create-news-valid' => ['controller' => 'NewsController', 'method' => 'createValid'],
    'edit-news'         => ['controller' => 'NewsController', 'method' => 'editForm'],
    'update-news'       => ['controller' => 'NewsController', 'method' => 'update'],
    'delete-news'       => ['controller' => 'NewsController', 'method' => 'delete'],

    // Auth
    'login'             => ['view' => __DIR__ . '/../App/Views/users/login.php', 'public' => true],
    'logout'            => ['controller' => 'AuthController', 'method' => 'logout'],
];

// Page demandée
$page = $_GET['page'] ?? 'home';

// Route inconnue vers page 404
if (!isset($routes[$page])) {
    include __DIR__ . '/../App/Views/404.php';
    exit;
}

$route = $routes[$page];

// vérification si accès public ou privé
$isPublic = $route['public'] ?? false;
if (!$isPublic && !$auth->isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

// Si c’est une simple vue
if (isset($route['view'])) {
    $data = [];

    // Si la route a un Data, on les récupère
    if (isset($route['data']) && is_callable($route['data'])) {
        $data = $route['data']($pdo);
    }

    // on rend les variables de Data disponibles dans la vue
    extract($data);

    include $route['view'];
    exit;
}

// Sinon, on appelle le contrôleur dynamiquement
$controllerName = $route['controller'];
$method = $route['method'];

$controller = new $controllerName($pdo);

// Cas spécial → besoin de l’ID utilisateur à compléter
if ($method === 'listRDV') {
    $controller->$method($_SESSION['user_id']);
} else {
    $controller->$method();
}
