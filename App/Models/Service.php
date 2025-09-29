<?php
class Service
{
    private int $id;
    private string $nom;
    private int $duree; // durée en minutes

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->nom = $data['nom'];
        $this->duree = (int)($data['duree'] ?? 30); // valeur par défaut 30 min si non définie
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

    // Setters
    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function setDuree(int $duree): void
    {
        $this->duree = $duree;
    }
}
