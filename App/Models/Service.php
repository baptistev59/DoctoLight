<?php
class Service
{
    private int $id;
    private string $nom;
    private int $duree; // durée en minutes
    private ?string $description;
    private bool $is_active;
    private ?string $image;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->nom = $data['nom'];
        $this->duree = (int)($data['duree'] ?? 30);
        $this->description = $data['description'] ?? null;
        $this->is_active = isset($data['is_active']) ? (bool)$data['is_active'] : true;
        $this->image = $data['image'] ?? null;
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDuree(): int
    {
        return $this->duree;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }


    // Setters

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setDuree(int $duree): void
    {
        $this->duree = $duree;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setActive(bool $active): void
    {
        $this->is_active = $active;
    }
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }
}
