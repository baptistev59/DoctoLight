<?php
class AppController
{
    private $pdo;
    private $auth;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->auth = new AuthController($pdo);
    }

    public function loadPage($page)
    {
        // Pages publiques
        $publicPages = ['login'];

        if (!in_array($page, $publicPages) && !$this->auth->isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }

        switch ($page) {
            case 'home':
                include __DIR__ . '/../Views/home.php'; // App\Views\home.php
                // include 'views/home.php';
                break;

            case 'rdv': // Liste des rdv pour un user
                $rdvController = new RDVController($this->pdo);
                $rdvController->listRDV($_SESSION['user_id']);
                break;

            case 'users': // Liste des users
                $userController = new UserController($this->pdo);
                $userController->listUsers();
                break;

            case 'services': // Liste des services
                $serviceController = new ServiceController($this->pdo);
                $serviceController->listServices();
                break;

            case 'news': // Liste des news
                $newsController = new NewsController($this->pdo);
                $newsController->list();
                break;

            case 'news_show': // Afficher une seule news
                $newsController = new NewsController($this->pdo);
                $newsController->show();
                break;

            case 'create-news': // Formulaire création
                $newsController = new NewsController($this->pdo);
                $newsController->create();
                break;

            case 'create-news-valid': // Validation création
                $newsController = new NewsController($this->pdo);
                $newsController->createValid();
                break;

            case 'edit-news': // Formulaire édition
                $newsController = new NewsController($this->pdo);
                $newsController->editForm();
                break;

            case 'update-news': // Validation édition
                $newsController = new NewsController($this->pdo);
                $newsController->update();
                break;

            case 'delete-news': // Supprimer une news
                $newsController = new NewsController($this->pdo);
                $newsController->delete();
                break;


            case 'login': // Accès à la page de Login
                include __DIR__ . '/../Views/users/login.php';
                break;

            case 'logout': // Déconnexion et retour à la page de login
                $this->auth->logout();
                header('Location: index.php?page=login');
                break;

            default:
                include 'views/404.php';
                break;
        }
    }
}
