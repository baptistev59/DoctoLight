<?php

class News
{
    private int $id;
    private string $titre;
    private string $contenu;
    private int $created_by;
    private DateTime $created_at;
    private ?string $image = null;

    public function __construct(array $data)
    {
        // Si id n’est pas fourni, on ne l’assigne pas
        if (isset($data['id'])) {
            $this->id = (int)$data['id'];
        };
        $this->titre = $data['titre'];
        $this->contenu = $data['contenu'];
        $this->created_by = $data['created_by'] ?? 1; // Désactive l'id obligatoire
        $this->created_at = isset($data['created_at'])
            ? new \DateTime($data['created_at'])
            : new \DateTime();
        $this->image = $data['image'] ?? null;
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }
    public function getTitre(): string
    {
        return $this->titre;
    }
    public function getContenu(): string
    {
        return $this->contenu;
    }
    public function getCreatedBy(): int
    {
        return $this->created_by;
    }
    public function getCreatedAt(string $format = 'd-m-Y H:i:s'): string
    {
        return $this->created_at->format($format);
    }
    public function getImage(): ?string
    {
        return $this->image;
    }

    // Setters
    public function setTitre(string $titre): void
    {
        $this->titre = $titre;
    }
    public function setContenu(string $contenu): void
    {
        $this->contenu = $contenu;
    }
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }
}
