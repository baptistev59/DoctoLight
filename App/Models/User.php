<?php

class User
{
    private int $id;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $passwordHash; // mot de passe hashé
    private ?string $date_naissance;
    private bool $is_active;
    private array $roles = [];       // liste des rôles du user
    private ?string $highestRole;    // rôle le plus élevé

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->nom = $data['nom'];
        $this->prenom = $data['prenom'];
        $this->email = $data['email'];
        $this->passwordHash = $data['password'];
        $this->date_naissance = $data['date_naissance'] ?? null;
        $this->is_active = (bool)$data['is_active'];
        $this->roles = $data['roles'] ?? [];
        $this->highestRole = $data['highest_role'] ?? $this->findHighestRole();
    }

    // === Getters ===
    public function getId(): int
    {
        return $this->id;
    }
    public function getNom(): string
    {
        return $this->nom;
    }
    public function getPrenom(): string
    {
        return $this->prenom;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
    public function getDateNaissance(): ?string
    {
        return $this->date_naissance;
    }
    public function isActive(): bool
    {
        return $this->is_active;
    }
    public function getRoles(): array
    {
        return $this->roles;
    }
    public function getHighestRole(): ?string
    {
        return $this->findHighestRole();
    }

    // === Roles ===
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
        $this->highestRole = $this->findHighestRole();
    }

    private function findHighestRole(): ?string
    {
        if (empty($this->roles)) return null;

        $config = require __DIR__ . '/../../Config/config.php';
        $hierarchy = $config['role_hierarchy'] ?? ['ADMIN', 'SECRETAIRE', 'MEDECIN', 'PATIENT'];

        foreach ($hierarchy as $roleName) {
            foreach ($this->roles as $role) {
                if ($role instanceof Role && $role->getName() === $roleName) {
                    return $roleName;
                }
            }
        }

        return null;
    }

    public function hasRole(string|array $roleName): bool
    {
        $rolesToCheck = is_array($roleName) ? $roleName : [$roleName];

        foreach ($this->roles as $role) {
            if ($role instanceof Role && in_array($role->getName(), $rolesToCheck)) {
                return true;
            }
        }
        return false;
    }
}
