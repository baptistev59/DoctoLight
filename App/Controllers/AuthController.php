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

    //  Connexion  //
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $user = $this->userManager->findByEmail($email);

            // var_dump($user->getPasswordHash());
            // var_dump(password_verify($password, $user->getPasswordHash()));
            //exit;

            // Vérification du password
            if ($user && password_verify($password, $user->getPasswordHash())) {
                // Stocker l'objet User complet en session
                $_SESSION['user'] = $user;

                header("Location: index.php?page=home");
                exit;
            } else {
                $error = "Email ou mot de passe incorrect";
                include __DIR__ . '/../Views/users/login.php';
            }
        } else {
            // include __DIR__ . '/../Views/users/login.php';
            include __DIR__ . '/../Views/users/login.php';
            echo "Le fichier login.php est inclus correctement";
            exit;
        }
    }

    // Déconnexion
    public function logout(): void
    {
        session_destroy();
        header("Location: index.php?page=home");
        exit;
    }

    // Vérifier si connecté
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user']) && $_SESSION['user'] instanceof User;
    }

    // Vérifier rôle
    public function hasRole(string $role): bool
    {
        return $this->isLoggedIn() && in_array($role, $_SESSION['user']->getRoles());
    }

    // Exiger un rôle minimum
    public function requireRole(string $role): void
    {
        if (!$this->isLoggedIn()) {
            header("HTTP/1.1 403 Forbidden");
            include __DIR__ . '/../Views/403.php';
            exit;
        }

        $config = require __DIR__ . '/../../Config/config.php';
        $hierarchy = $config['role_hierarchy'];

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
            $data = [
                'nom'      => $_POST['nom'] ?? '',
                'prenom'   => $_POST['prenom'] ?? '',
                'email'    => $_POST['email'] ?? '',
                'password' => $_POST['password'], // Hashage dans le UserManager
                'date_naissance' => $_POST['date_naissance'] ?? null,
                'is_active' => 1,
                'roles'    => ['PATIENT'] // par défaut un patient
            ];

            $newUser = $this->userManager->createUser($data);

            if ($newUser instanceof User) {
                // même logique que login
                $_SESSION['user'] = $newUser;

                header('Location: index.php?page=home');
                exit;
            } else {
                $error = "Erreur lors de l'inscription.";
            }
        }

        include __DIR__ . '/../Views/users/register.php';
    }
}
