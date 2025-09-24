<?php
class UserController
{
    private PDO $pdo;
    private UserManager $userManager;
    private AuthController $authController;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->userManager = new UserManager($pdo, $config);
        $this->authController = new AuthController($pdo);
    }

    // Liste des utilisateurs (admin)
    public function listUsers(): void
    {
        $this->authController->requireRole('ADMIN'); // admin only

        $users = $this->userManager->findAll();
        include __DIR__ . '/../Views/users/list.php';
    }

    // Création d’un utilisateur (admin uniquement)
    public function create(): void
    {
        $this->authController->requireRole('ADMIN'); // admin only

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom'            => $_POST['nom'] ?? '',
                'prenom'         => $_POST['prenom'] ?? '',
                'email'          => $_POST['email'] ?? '',
                'password'       => $_POST['password'], // Hashage dans le UserManegr
                'date_naissance' => $_POST['date_naissance'] ?? null,
                'is_active'      => isset($_POST['is_active']) ? 1 : 0,
                'roles'          => $_POST['roles'] ?? []
            ];

            $newUser = $this->userManager->createUser($data);

            if ($newUser instanceof User) {
                $_SESSION['success'] = "Utilisateur {$newUser->getPrenom()} {$newUser->getNom()} créé avec succès.";
                header("Location: index.php?page=users");
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la création de l’utilisateur.";
            }
        }

        include __DIR__ . '/../Views/users/create.php';
    }

    // Édition d’un utilisateur (admin uniquement)
    public function edit(int $id): void
    {
        $this->authController->requireRole('ADMIN'); // admin only

        $user = $this->userManager->findById($id);

        if (!$user) {
            header("HTTP/1.0 404 Not Found");
            echo "Utilisateur introuvable.";
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom'            => $_POST['nom'] ?? '',
                'prenom'         => $_POST['prenom'] ?? '',
                'email'          => $_POST['email'] ?? '',
                'date_naissance' => $_POST['date_naissance'] ?? null,
                'is_active'      => isset($_POST['is_active']) ? 1 : 0,
                'roles'          => $_POST['roles'] ?? [],
            ];

            // Mot de passe modifiable uniquement si rempli
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            $updatedUser = $this->userManager->updateUser($user, $data);

            if ($updatedUser instanceof User) {
                $_SESSION['success'] = "Utilisateur mis à jour avec succès.";
                header("Location: index.php?page=users");
                exit;
            } else {
                $error = "Erreur lors de la mise à jour de l’utilisateur.";
            }
        }

        include __DIR__ . '/../Views/users/edit.php';
    }
}
