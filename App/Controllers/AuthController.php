<?php

declare(strict_types=1);

class AuthController extends BaseController
{

    public function __construct(PDO $pdo, ?array $config = null)
    {
        parent::__construct($pdo);

        // On réutilise la config passée depuis BaseController si dispo
        if ($config !== null && is_array($config)) {
            $this->config = $config;
        } else {
            // Fallback si AuthController est appelé seul (ex: init.php)
            $configPath = dirname(__DIR__, 2) . '/Config/config.php';
            $loaded = (file_exists($configPath)) ? require $configPath : [];
            $this->config = is_array($loaded) ? $loaded : [];
        }
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
                // Connexion réussie
                session_regenerate_id(true); // protection fixation de session
                $_SESSION['user'] = $user;

                // === AUDIT ===
                $this->audit('users', $user->getId(), 'LOGIN', 'Connexion réussie');

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

        // === AUDIT ===
        $user = $_SESSION['user'] ?? null;
        $userId = ($user && method_exists($user, 'getId')) ? $user->getId() : null;
        if ($userId) {
            $this->audit('users', $userId, 'LOGOUT', 'Déconnexion de l\'utilisateur');
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

        // Redirection vers accueil
        header("Location: index.php?page=home");
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
    public function requireRole(string|array $roles): void
    {
        if (!$this->isLoggedIn()) {
            header("HTTP/1.1 403 Forbidden");
            include __DIR__ . '/../Views/403.php';
            exit;
        }

        $hierarchy = $this->config['role_hierarchy'];
        $userRole  = $_SESSION['user']->getHighestRole();

        // Si $roles est une string, on la transforme en tableau
        $roles = (array) $roles;

        $allowed = false;
        foreach ($roles as $role) {
            if ($userRole && array_search($userRole, $hierarchy) <= array_search($role, $hierarchy)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
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

                // === AUDIT ===
                $this->audit('users', $newUser->getId(), 'INSERT', 'Inscription d\'un nouveau patient');

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
