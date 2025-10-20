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

// Instance unique de AuthController
$auth = new AuthController($pdo, $config);

// Routes
$routes = [
    'home' => [
        'view' => 'home',
        'public' => true,
        'data' => fn($pdo) => [
            'news' => (new NewsController($pdo, $config))->getLatestNews(5),
            'services' => (new ServiceManager($pdo))->getActiveServices()
        ]
    ],
    // page de test Vue simple
    'test_view' => [
        'view' => 'test',
        'public' => true,
        'data' => fn($pdo) => ['message' => 'Hello World!']
    ],

    // page de Test de redirection
    'test-redirect' => ['controller' => 'AuthController', 'method' => 'login', 'public' => true],

    // À propos
    'apropos' => ['view' => 'about', 'public' => true],

    // Page d'accueil
    'home' => ['controller' => 'HomeController', 'method' => 'index', 'public' => true],

    // RDV
    'rdv' => ['controller' => 'RDVController', 'method' => 'planning', 'role' => ['MEDECIN', 'SECRETAIRE']],
    'create_rdv' => ['controller' => 'RDVController', 'method' => 'create'],
    'create_rdv_valid' => ['controller' => 'RDVController', 'method' => 'createValid'],
    'rdv_store' => ['controller' => 'RDVController', 'method' => 'store', 'role' => ['PATIENT', 'SECRETAIRE']],
    'rdv_listpatient' => ['controller' => 'RDVController', 'method' => 'listPatient', 'role' => 'PATIENT'],
    'rdv_cancel' => ['controller' => 'RDVController', 'method' => 'rdvCancel', 'role' => ['PATIENT', 'SECRETAIRE']],
    'rdv_edit'   => ['controller' => 'RDVController', 'method' => 'rdvEdit', 'role' => ['PATIENT', 'SECRETAIRE']],



    // Users
    'users' => ['controller' => 'UserController', 'method' => 'listUsers', 'role' => 'ADMIN'],
    'users_create' => ['controller' => 'UserController', 'method' => 'create', 'role' => 'ADMIN'],
    'users_edit' => ['controller' => 'UserController', 'method' => 'edit'],
    'users_delete' => ['controller' => 'UserController', 'method' => 'delete', 'role' => 'ADMIN'],
    'users_view' => ['controller' => 'UserController', 'method' => 'view'],
    'users_toggle' => ['controller' => 'UserController', 'method' => 'toggleActive', 'role' => 'ADMIN'],
    'profile' => ['controller' => 'UserController', 'method' => 'profile'],

    // Services
    'services' => ['controller' => 'ServiceController', 'method' => 'list', 'role' => ['ADMIN', 'SECRETAIRE', 'MEDECIN']],
    'service_create' => ['controller' => 'ServiceController', 'method' => 'create', 'role' => ['ADMIN', 'SECRETAIRE']],
    'services_store' => ['controller' => 'ServiceController', 'method' => 'store', 'role' => ['ADMIN', 'SECRETAIRE']],
    'services_edit' => ['controller' => 'ServiceController', 'method' => 'edit',  'role' => ['ADMIN', 'SECRETAIRE']],
    'services_update' => ['controller' => 'ServiceController', 'method' => 'update', 'role' => ['ADMIN', 'SECRETAIRE']],
    'services_delete' => ['controller' => 'ServiceController', 'method' => 'delete', 'role' => ['ADMIN', 'SECRETAIRE']],
    'services_toggle'  => ['controller' => 'ServiceController', 'method' => 'toggleActive', 'role' => ['SECRETAIRE', 'ADMIN']],
    'service_show' => ['controller' => 'ServiceController', 'method' => 'show', 'public' => true],


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

    // Disponibilités Services
    'dispo_services' => ['controller' => 'DisponibiliteServiceController', 'method' => 'list', 'role' => ['ADMIN', 'SECRETAIRE']],
    'dispo_service_create' => ['controller' => 'DisponibiliteServiceController', 'method' => 'create', 'role' => ['ADMIN', 'SECRETAIRE']],
    'dispo_service_store'  => ['controller' => 'DisponibiliteServiceController', 'method' => 'store', 'role' => ['ADMIN', 'SECRETAIRE']],
    'dispo_service_edit'   => ['controller' => 'DisponibiliteServiceController', 'method' => 'edit', 'role' => ['ADMIN', 'SECRETAIRE']],
    'dispo_service_update' => ['controller' => 'DisponibiliteServiceController', 'method' => 'update', 'role' => ['ADMIN', 'SECRETAIRE']],
    'dispo_service_delete' => ['controller' => 'DisponibiliteServiceController', 'method' => 'delete', 'role' => ['ADMIN', 'SECRETAIRE']],


    // DISPONIBILITÉS STAFF

    'dispo_staff_list' => ['controller' => 'DisponibiliteStaffController', 'method' => 'list', 'role' => ['ADMIN', 'SECRETAIRE']],
    'dispo_staff_store' => ['controller' => 'DisponibiliteStaffController', 'method' => 'store', 'role' => ['ADMIN', 'SECRETAIRE', 'MEDECIN']],
    'dispo_staff_update' => ['controller' => 'DisponibiliteStaffController', 'method' => 'update', 'role' => ['ADMIN', 'SECRETAIRE', 'MEDECIN']],
    'dispo_staff_delete' => ['controller' => 'DisponibiliteStaffController', 'method' => 'delete', 'role' => ['ADMIN', 'SECRETAIRE', 'MEDECIN']],



    // Fermeture exceptionnelle
    'fermetures'        => ['controller' => 'FermetureController', 'method' => 'list', 'role' => ['ADMIN', 'SECRETAIRE']],
    'fermeture_store'   => ['controller' => 'FermetureController', 'method' => 'store', 'role' => ['ADMIN', 'SECRETAIRE']],
    'fermeture_delete'  => ['controller' => 'FermetureController', 'method' => 'delete', 'role' => ['ADMIN', 'SECRETAIRE']],

    // Audit
    'auditlogs' => ['controller' => 'AuditLogController', 'method' => 'list', 'role' => ['ADMIN']],
    'auditlogs_clean' => ['controller' => 'AuditLogController', 'method' => 'clean', 'role' => ['ADMIN']],


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

    // Instanciation du contrôleur
    $controller = new $controllerName($pdo);

    // Injection du AuthController dans le contrôleur
    if (method_exists($controller, 'setAuthController')) {
        $controller->setAuthController($auth);
    }

    // Méthodes nécessitant ID
    $methodsRequiringId = ['edit', 'delete', 'view', 'toggleActive', 'rdvEdit', 'rdvCancel', 'show', 'update'];
    if (in_array($method, $methodsRequiringId, true)) {
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
