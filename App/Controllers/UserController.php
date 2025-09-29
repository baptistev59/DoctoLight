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

        // Vérification du CSRF token
        $this->authController->checkCsrfToken();

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

    // Affichage d’un utilisateur (admin, médecin, secrétaire ou le patient lui-même)
    public function view(int $id): void
    {
        $currentUser = $_SESSION['user'] ?? null;

        if (!$currentUser instanceof User) {
            header("Location: index.php?page=login");
            exit;
        }

        $isAdminOrStaff = $this->authController->hasRole(['ADMIN', 'MEDECIN', 'SECRETAIRE']);

        // Patient ne peut voir que sa propre fiche
        if (!$isAdminOrStaff && $currentUser->getId() !== $id) {
            header("HTTP/1.0 403 Forbidden");
            echo "Accès interdit.";
            exit;
        }

        $userToView = $this->userManager->findById($id);
        if (!$userToView) {
            header("HTTP/1.0 404 Not Found");
            echo "Utilisateur introuvable.";
            exit;
        }

        // Récupérer les rôles uniquement si admin ou staff
        $roles = [];
        if ($isAdminOrStaff) {
            $roles = $this->userManager->getAllRoles();
        }

        include __DIR__ . '/../Views/users/profile.php';
    }

    // Activer / Désactiver un utilisateur
    public function toggleActive(int $id): void
    {
        $this->authController->requireRole('ADMIN'); // admin only

        // Vérification du CSRF token
        $this->authController->checkCsrfToken();

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

        // Vérification du CSRF token
        $this->authController->checkCsrfToken();

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

    /* 
    Édition d’un utilisateur (admin, médecin, secrétaire ou le patient lui-même)
    Admin → peut modifier tous les profils.
    Médecin → peut modifier uniquement : les patients, sa propre fiche
    Secrétaire → peut modifier uniquement : les patients, les médecins, sa propre fiche
    Patient → uniquement sa propre fiche */
    public function edit(int $id): void
    {
        $userLogged = $_SESSION['user'] ?? null;
        if (!$userLogged) {
            header("Location: index.php?page=login");
            exit;
        }

        $userToEdit = $this->userManager->findById($id);
        if (!$userToEdit) {
            header("HTTP/1.0 404 Not Found");
            echo "Utilisateur introuvable.";
            exit;
        }

        // Récupération des rôles
        $rolesLogged = array_map(fn($r) => $r->getName(), $userLogged->getRoles());
        $rolesToEdit = array_map(fn($r) => $r->getName(), $userToEdit->getRoles());

        $canEdit = false;

        // Vérification des droits
        if (in_array('ADMIN', $rolesLogged, true)) {
            // Admin peut tout modifier
            $canEdit = true;
        } elseif (in_array('MEDECIN', $rolesLogged, true)) {
            // Médecin peut modifier les patients + sa propre fiche
            if (in_array('PATIENT', $rolesToEdit, true) || $userLogged->getId() === $id) {
                $canEdit = true;
            }
        } elseif (in_array('SECRETAIRE', $rolesLogged, true)) {
            // Secrétaire peut modifier patients, médecins, et sa propre fiche
            if (in_array('PATIENT', $rolesToEdit, true) || in_array('MEDECIN', $rolesToEdit, true) || $userLogged->getId() === $id) {
                $canEdit = true;
            }
        } elseif (in_array('PATIENT', $rolesLogged, true)) {
            // Patient peut uniquement modifier sa propre fiche
            if ($userLogged->getId() === $id) {
                $canEdit = true;
            }
        }

        if (!$canEdit) {
            $_SESSION['error'] = "Vous n'avez pas les droits pour modifier ce profil.";
            header("Location: index.php?page=users");
            exit;
        }

        // Traitement requête POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->authController->checkCsrfToken();

            $data = [
                'nom'            => $_POST['nom'] ?? '',
                'prenom'         => $_POST['prenom'] ?? '',
                'email'          => $_POST['email'] ?? '',
                'date_naissance' => $_POST['date_naissance'] ?? null,
            ];

            // Seuls admin / secrétaire / médecin peuvent modifier "is_active" et "roles"
            if (in_array('ADMIN', $rolesLogged, true) || in_array('SECRETAIRE', $rolesLogged, true) || in_array('MEDECIN', $rolesLogged, true)) {
                $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
                $data['roles']     = $_POST['roles'] ?? [];
            }

            // Mot de passe modifiable uniquement si rempli
            if (!empty($_POST['password'])) {
                if ($_POST['password'] !== ($_POST['password_confirm'] ?? '')) {
                    $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
                    header("Location: index.php?page=users_edit&id=" . $id);
                    exit;
                }
                $data['password'] = $_POST['password'];
                $data['password_confirm'] = $_POST['password_confirm'];
            }


            $updatedUser = $this->userManager->updateUser($userToEdit, $data);

            if ($updatedUser instanceof User) {
                $_SESSION['success'] = "Profil mis à jour avec succès.";

                // Redirections
                if (in_array('ADMIN', $rolesLogged, true)) {
                    // Admin modifie sa propre fiche -> profil
                    if ($userLogged->getId() === $id) {
                        header("Location: index.php?page=profile");
                    } else {
                        // Admin modifie un autre -> fiche de cet utilisateur
                        header("Location: index.php?page=users_view&id=" . $id);
                    }
                } else {
                    // Médecin, secrétaire, patient -> toujours fiche de l'utilisateur édité
                    header("Location: index.php?page=users_view&id=" . $id);
                }
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour.";
            }
        }

        // Récupérer les rôles disponibles seulement si admin
        $roles = [];
        if (in_array('ADMIN', $rolesLogged, true)) {
            $roles = $this->userManager->getAllRoles();
        }

        include __DIR__ . '/../Views/users/edit.php';
    }


    // Suppression d'un utilisateur
    public function delete(int $id): void
    {
        $this->authController->requireRole('ADMIN'); // admin only
        // var_dump('UserController delete user id : ' . $id);
        // Vérification du CSRF token
        $this->authController->checkCsrfToken();

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
            $_SESSION['error'] = "Impossible de supprimer cet utilisateur : il a au moins un RDV.";
        }

        header("Location: index.php?page=users");
        exit;
    }

    public function profile(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . 'index.php?page=login');
            exit;
        }
        // On résupère le User connecté
        $currentUser = $_SESSION['user'];


        // Cas 1 : si un ID est passé en paramètre ET que l’utilisateur est admin/staff
        $id = $_GET['id'] ?? null;

        if ($id !== null && $currentUser->hasRole(['ADMIN', 'MEDECIN', 'SECRETAIRE'])) {
            $userManager = new UserManager($this->pdo, []);
            $userToView = $userManager->findById((int)$id);

            if (!$userToView) {
                $_SESSION['error'] = "Utilisateur introuvable.";
                header("Location: index.php?page=users");
                exit;
            }
        } else {
            // Cas 2 : pas d’ID → affiche son propre profil
            $userToView = $currentUser;
        }

        include __DIR__ . '/../Views/users/profile.php';
    }
}
