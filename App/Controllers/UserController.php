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

    // Liste des utilisateurs (admin uniquement) avec recherche, tri et pagination
    public function listUsers(): void
    {
        $this->authController->requireRole('ADMIN'); // admin only

        // Récupérer les filtres depuis $_GET
        $search    = $_GET['search'] ?? '';
        $sort      = $_GET['sort'] ?? 'id';
        $order     = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $pageNum   = max(1, (int)($_GET['page_num'] ?? 1));
        $perPage   = 10;
        $offset    = ($pageNum - 1) * $perPage;

        // Récupérer les utilisateurs filtrés
        $result = $this->userManager->findAllWithFilters($search, $sort, $order, $perPage, $offset);

        $users      = $result['users'];
        $totalUsers = $result['totalUsers'] ?? 0;
        $totalPages = (int)ceil($totalUsers / $perPage);

        include __DIR__ . '/../Views/users/list.php';
    }

    // Affichage d’un utilisateur (admin uniquement)
    public function view(int $id): void
    {
        $this->authController->requireRole('ADMIN'); // admin only

        $userToView = $this->userManager->findById($id);
        if (!$userToView) {
            header("HTTP/1.0 404 Not Found");
            echo "Utilisateur introuvable.";
            exit;
        }

        // Récupérer tous les rôles disponibles pour l'affichage (optionnel)
        $roles = $this->userManager->getAllRoles();

        include __DIR__ . '/../Views/users/profile.php';
    }

    // Activer / Désactiver un utilisateur
    public function toggleActive(int $id): void
    {
        $this->authController->requireRole('ADMIN'); // admin only

        // Evite de désactiver son propre compte
        if ($id === $_SESSION['user']->getId()) {
            $_SESSION['error'] = "Vous ne pouvez pas désactiver votre propre compte.";
            header("Location: index.php?page=users");
            exit;
        }

        $user = $this->userManager->findById($id);
        if ($user) {
            $this->userManager->toggleActive($user);
            $_SESSION['success'] = "Utilisateur mis à jour.";
        } else {
            $_SESSION['error'] = "Utilisateur introuvable.";
        }

        header("Location: index.php?page=users");
        exit;
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

            die;

            if ($updatedUser instanceof User) {
                $_SESSION['success'] = "Utilisateur mis à jour avec succès.";
                header("Location: index.php?page=users");
                exit;
            } else {
                $error = "Erreur lors de la mise à jour de l’utilisateur.";
            }
        }
        // Récupérer tous les rôles disponibles pour afficher les checkbox
        $rolesData = $this->userManager->getAllRoles();
        $roles = [];
        foreach ($rolesData as $r) {
            $roles[] = new Role($r);
        }

        include __DIR__ . '/../Views/users/edit.php';
    }

    // Suppression d'un utilisateur
    public function delete(int $id): void
    {
        $this->authController->requireRole('ADMIN'); // admin only

        // Evite la suppression de son compte Admin
        if ($id === $_SESSION['user']->getId()) {
            $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte.";
            header("Location: index.php?page=users");
            exit;
        }

        $deleted = $this->userManager->deleteUser($id);

        if ($deleted) {
            $_SESSION['success'] = "Utilisateur supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur.";
        }

        header("Location: index.php?page=users");
        exit;
    }
}
