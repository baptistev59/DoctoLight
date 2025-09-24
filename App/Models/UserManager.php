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

        // Charger les rôles
        $roles = $this->getRoles((int)$data['id']);
        $data['roles'] = $roles;

        // Déterminer rôle le plus élevé
        $data['highest_role'] = $this->determineHighestRole($roles);

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
            $this->pdo->beginTransaction();

            // Supprimer d’abord les rôles
            $this->pdo->prepare("DELETE FROM user_roles WHERE user_id = :id")
                ->execute([':id' => $id]);

            // Supprimer l’utilisateur
            $request = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
            $result = $request->execute([':id' => $id]);

            $this->pdo->commit();
            return $result;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur suppression utilisateur : " . $e->getMessage());
            return false;
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
        $sql = "SELECT r.name
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :user_id";
        $params = [':user_id' => $userId];

        $request = $this->pdo->prepare($sql);
        $request->execute($params);

        // Retourne un tableau simple ['ADMIN', 'MEDECIN']
        return $request->fetchAll(PDO::FETCH_COLUMN);
    }

    // Déterminer le rôle le plus élevé d’un tableau de rôles
    private function determineHighestRole(array $roles): ?string
    {
        $hierarchy = $this->config['role_hierarchy'] ?? ['ADMIN', 'SECRETAIRE', 'MEDECIN', 'PATIENT'];

        foreach ($hierarchy as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }
        return null; // Aucun rôle trouvé
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
}
