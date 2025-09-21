<?php

/* On ouvre la session dès l'accès au site */
session_start();
// require __DIR__ . '/../Config/Database.php';

/* Autoload des classes PHP (models et controllers) dès qu'on les utilise */
spl_autoload_register(function ($class) {
    $paths = [                          // Public\index.php
        __DIR__ . '/../Config/',
        __DIR__ . '/../App/Controllers/', //App\Controllers\AppController.php
        __DIR__ . '/../App/Models/',
        __DIR__ . '/../utils/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) require $file;
    }
});

// Connexion DB
$db = new Database();
$pdo = $db->getConnection();

// Instanciation du contrôleur principal
$app = new AppController($pdo);

// Récupère la page demandée
$page = $_GET['page'] ?? 'home';
$app->loadPage($page);
