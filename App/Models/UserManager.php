<?php

class UserManager
{
    private PDO $pdo;
    private array $config;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    // Récupérer un utilisateur par son ID
    public function findById(int $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $request = $this->pdo->prepare($sql);
        $request->execute([':id' => $id]);
        $data = $request->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        // Charger les rôles de l'utilisateur
        $roles = $this->getRoles((int)$data['id']);
        $data['roles'] = $roles;

        // Déterminer le rôle le plus élevé
        $data['highest_role'] = $this->determineHighestRole($roles);

        // Retourner un objet User
        return new User($data);
    }


    // Récupérer un utilisateur par email
    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $params = [':email' => $email];

        $request = $this->pdo->prepare($sql);
        $request->execute($params);
        $data = $request->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null; // Aucun utilisateur trouvé
        }

        // Charger les rôles de l'utilisateur
        $roles = $this->getRoles((int)$data['id']);
        $data['roles'] = $roles;

        // Déterminer le rôle le plus élevé
        $data['highest_role'] = $this->determineHighestRole($roles);

        // Retourner un objet User
        return new User($data);
    }

    // Récupérer tous les utilisateurs
    public function findAll(): array
    {
        $sql = "SELECT * FROM users ORDER BY id DESC";
        $request = $this->pdo->query($sql);
        $usersData = $request->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($usersData as $data) {
            $roles = $this->getRoles((int)$data['id']);
            $data['roles'] = $roles;
            $data['highest_role'] = $this->determineHighestRole($roles);

            $users[] = new User($data);
        }

        return $users;
    }

    // Récupérer les utilisateurs avec recherche, tri et pagination
    public function findAllWithFilters(string $search = '', string $sort = 'id', string $order = 'ASC', int $limit = 10, int $offset = 0): array
    {
        $allowedSort = ['id', 'nom', 'prenom', 'email'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'id';
        }

        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $params = [];
        $sql = "SELECT * FROM users WHERE 1";

        if ($search !== '') {
            $sql .= " AND (nom LIKE :search OR prenom LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY $sort $order LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($usersData as $data) {
            $roles = $this->getRoles((int)$data['id']);
            $data['roles'] = $roles;
            $data['highest_role'] = $this->determineHighestRole($roles);
            $users[] = new User($data);
        }

        // Calcul du nombre total pour la pagination
        $countSql = "SELECT COUNT(*) FROM users WHERE 1";
        if ($search !== '') {
            $countSql .= " AND (nom LIKE :search OR prenom LIKE :search OR email LIKE :search)";
        }
        $countStmt = $this->pdo->prepare($countSql);
        if ($search !== '') {
            $countStmt->bindValue(':search', "%$search%");
        }
        $countStmt->execute();
        $totalUsers = (int)$countStmt->fetchColumn();
        $totalPages = ceil($totalUsers / $limit);

        return [
            'users'      => $users,
            'totalPages' => $totalPages,
            'totalPages' => $totalPages,
        ];
    }

    // Activer / désactiver un utilisateur
    public function toggleActive(User $user): void
    {
        $newStatus = $user->isActive() ? 0 : 1;
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $newStatus,
            ':id'     => $user->getId(),
        ]);
    }

    public function setActive(User $user, bool $active): void
    {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $active ? 1 : 0,
            ':id'     => $user->getId(),
        ]);
    }


    // Créer un utilisateur
    public function createUser(array $data): ?User
    {
        try {
            $this->pdo->beginTransaction();

            $request = $this->pdo->prepare("
            INSERT INTO users (nom, prenom, email, password, date_naissance, is_active)
            VALUES (:nom, :prenom, :email, :password, :date_naissance, :is_active)
        ");
            $request->execute([
                ':nom'            => $data['nom'],
                ':prenom'         => $data['prenom'],
                ':email'          => $data['email'],
                ':password'       => password_hash($data['password'], PASSWORD_DEFAULT),
                ':date_naissance' => $data['date_naissance'] ?? null,
                ':is_active'      => $data['is_active'] ?? 1,
            ]);

            $userId = (int)$this->pdo->lastInsertId();

            // Ajouter les rôles
            foreach ($data['roles'] as $roleName) {
                $roleId = $this->getRoleIdByName($roleName);
                if ($roleId) {
                    $requestRole = $this->pdo->prepare("
                    INSERT INTO user_roles (user_id, role_id)
                    VALUES (:user_id, :role_id)
                ");
                    $requestRole->execute([
                        ':user_id' => $userId,
                        ':role_id' => $roleId,
                    ]);
                }
            }

            $this->pdo->commit();

            // Retourne l’objet User
            return $this->findById($userId);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur création utilisateur : " . $e->getMessage());
            return null;
        }
    }


    // Supprime un user avec rollback
    public function deleteUser(int $id): bool
    {
        try {
            // Vérifier si l'utilisateur a des RDV
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM rdv WHERE patient_id = :id");
            $stmt->execute([':id' => $id]);
            $rdvCount = (int)$stmt->fetchColumn();

            if ($rdvCount > 0) {
                // L'utilisateur a au moins un RDV, ne pas supprimer
                error_log("L'utilisateur a au moin un rendez-vous !");
                return false;
            }
            $this->pdo->beginTransaction();

            // Supprimer d’abord les rôles
            $this->pdo->prepare("DELETE FROM user_roles WHERE user_id = :id")
                ->execute([':id' => $id]);

            // Supprimer l’utilisateur
            $request = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
            $result = $request->execute([':id' => $id]);

            // var_dump('UserManager après delete execute user result : ' . $result);
            // die;
            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur suppression utilisateur : " . $e->getMessage());
            var_dump($e->getMessage());
            die;
            return false;
        }
    }



    // mise à jour du user avec un rollback
    public function updateUser(User $user, array $data): ?User
    {
        try {
            $this->pdo->beginTransaction();

            $query = "UPDATE users SET nom = :nom, prenom = :prenom, email = :email, 
                  date_naissance = :date_naissance, is_active = :is_active";
            $params = [
                ':nom'            => $data['nom'],
                ':prenom'         => $data['prenom'],
                ':email'          => $data['email'],
                ':date_naissance' => $data['date_naissance'] ?? null,
                ':is_active'      => $data['is_active'],
                ':id'             => $user->getId(),
            ];

            if (!empty($data['password'])) {
                $query .= ", password = :password";
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $query .= " WHERE id = :id";

            $request = $this->pdo->prepare($query);
            $request->execute($params);

            // Supprimer les anciens rôles
            $this->pdo->prepare("DELETE FROM user_roles WHERE user_id = :id")
                ->execute([':id' => $user->getId()]);

            // Réinsérer les nouveaux rôles
            foreach ($data['roles'] as $roleName) {
                $roleId = $this->getRoleIdByName($roleName);
                if ($roleId) {
                    $requestRole = $this->pdo->prepare("
                    INSERT INTO user_roles (user_id, role_id)
                    VALUES (:user_id, :role_id)
                ");
                    $requestRole->execute([
                        ':user_id' => $user->getId(),
                        ':role_id' => $roleId,
                    ]);
                }
            }

            $this->pdo->commit();

            // Retourne l’objet User mis à jour
            return $this->findById($user->getId());
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur mise à jour utilisateur : " . $e->getMessage());
            return null;
        }
    }


    // Cherche un rôle par son nom
    private function getRoleIdByName(string $roleName): ?int
    {
        $sql = "SELECT id FROM roles WHERE name = :name LIMIT 1";
        $request = $this->pdo->prepare($sql);
        $request->execute([':name' => $roleName]);
        $id = $request->fetchColumn();

        return $id ? (int)$id : null;
    }

    // Récupérer les rôles d’un utilisateur
    public function getRoles(int $userId): array
    {
        $sql = "SELECT r.id, r.name
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :user_id";
        $params = [':user_id' => $userId];

        $request = $this->pdo->prepare($sql);
        $request->execute($params);


        $rolesData = $request->fetchAll(PDO::FETCH_ASSOC);

        $roles = array_map(fn($r) => new Role($r), $rolesData);

        return $roles;
    }

    // Déterminer le rôle le plus élevé d’un tableau de rôles
    private function determineHighestRole(array $roles): ?string
    {
        $hierarchy = $this->config['role_hierarchy'] ?? ['ADMIN', 'SECRETAIRE', 'MEDECIN', 'PATIENT'];

        foreach ($hierarchy as $roleName) {
            foreach ($roles as $roleObj) {
                if ($roleObj instanceof Role && $roleObj->getName() === $roleName) {
                    return $roleName;
                }
            }
        }

        return null; // Aucun rôle trouvé
    }

    // Récupère tous les rôle pour les chekbox
    public function getAllRoles(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM roles");
        $rolesData =  $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($r) => new Role($r), $rolesData);
    }
}
