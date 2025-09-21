<?php

class News
{
    private int $id;
    private string $titre;
    private string $contenu;
    private int $created_by;
    private string $created_at;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->titre = $data['titre'];
        $this->contenu = $data['contenu'];
        $this->created_by = $data['created_by'];
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
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
    public function getCreatedAt(): string
    {
        return $this->created_at;
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
}
