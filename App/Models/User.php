<?php

class User
{
    private int $id;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $passwordHash; // mot de passe Hashé
    private ?string $date_naissance;
    private bool $is_active;
    private array $roles = [];       // tous les rôles du user
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
        $this->highestRole = $this->computeHighestRole();
    }

    // ------------------ Getters ------------------ //
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
        return $this->highestRole;
    }

    // ------------------ Roles ------------------ //
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
        $this->highestRole = $this->computeHighestRole();
    }

    private function computeHighestRole(): ?string
    {
        if (empty($this->roles)) return null;

        $config = require __DIR__ . '/../../Config/config.php';
        $hierarchy = $config['role_hierarchy'];

        foreach ($hierarchy as $role) {
            if (in_array($role, $this->roles, true)) {
                return $role;
            }
        }

        return null;
    }
}
