<?php
class AuthController
{
    private PDO $pdo;
    private UserManager $userManager;
    private array $config;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->config = require __DIR__ . '/../../Config/config.php';
        $this->userManager = new UserManager($pdo, $this->config);
    }

    // Vérification du Token CSRF
    public function checkCsrfToken(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                exit("Invalid CSRF token");
            }
        }
    }

    // Connexion
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrfToken();

            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $user = $this->userManager->findByEmail($email);

            if (!$user || !password_verify($password, $user->getPasswordHash())) {
                $error = "Adresse email ou mot de passe incorrect.";
            } elseif (!$user->isActive()) {
                $error = "Votre compte est désactivé. Contactez l'administrateur.";
            } else {
                session_regenerate_id(true); // protection fixation de session
                $_SESSION['user'] = $user;
                header("Location: index.php?page=home");
                exit;
            }

            include __DIR__ . '/../Views/users/login.php';
            exit;
        }

        $error = "";
        include __DIR__ . '/../Views/users/login.php';
        exit;
    }

    // Déconnexion sécurisée
    public function logout(): void
    {
        // Assurer que la session est démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vider les variables de session
        $_SESSION = [];

        // Supprimer le cookie de session (PHPSESSID)
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Détruire complètement la session
        session_destroy();

        // Redirection vers login ou accueil
        header("Location: index.php?page=login");
        exit;
    }

    // Vérifier si connecté
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user']) && $_SESSION['user'] instanceof User;
    }

    // Vérifier un ou plusieurs rôles
    public function hasRole(string|array $roles): bool
    {
        return $this->isLoggedIn() && $_SESSION['user']->hasRole($roles);
    }

    // Exiger un rôle minimum (selon la hiérarchie)
    public function requireRole(string $role): void
    {
        if (!$this->isLoggedIn()) {
            header("HTTP/1.1 403 Forbidden");
            include __DIR__ . '/../Views/403.php';
            exit;
        }

        $hierarchy = $this->config['role_hierarchy'];
        $userRole = $_SESSION['user']->getHighestRole();

        if (!$userRole || array_search($userRole, $hierarchy) > array_search($role, $hierarchy)) {
            header("HTTP/1.1 403 Forbidden");
            include __DIR__ . '/../Views/403.php';
            exit;
        }
    }

    // Inscription d'un patient
    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkCsrfToken();

            $email = trim($_POST['email']);

            // Vérifier si l'email existe déjà
            $existingUser = $this->userManager->findByEmail($email);
            if ($existingUser) {
                $error = "Un compte existe déjà avec cette adresse email.";
                include __DIR__ . '/../Views/users/register.php';
                exit;
            }

            // Vérifier correspondance mot de passe + confirmation
            if (empty($_POST['password_confirm']) || $_POST['password'] !== $_POST['password_confirm']) {
                $error = "Les mots de passe ne correspondent pas.";
                include __DIR__ . '/../Views/users/register.php';
                exit;
            }

            // Préparation des données
            $data = [
                'nom'      => $_POST['nom'] ?? '',
                'prenom'   => $_POST['prenom'] ?? '',
                'email'    => $_POST['email'] ?? '',
                'password' => $_POST['password'], // hashage dans UserManager
                'date_naissance' => $_POST['date_naissance'] ?? null,
                'is_active' => 1,
                'roles'    => ['PATIENT'] // Obligatoirement comme PATIENT
            ];

            $newUser = $this->userManager->createUser($data);

            if ($newUser instanceof User) {
                session_regenerate_id(true);
                $_SESSION['user'] = $newUser;
                header('Location: index.php?page=home');
                exit;
            } else {
                $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                include __DIR__ . '/../Views/users/register.php';
                exit;
            }
        }

        // Génération du token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $error = "";
        include __DIR__ . '/../Views/users/register.php';
        exit;
    }
}
