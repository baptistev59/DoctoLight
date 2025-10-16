<?php
class Fermeture
{
    private ?int $id;
    private string $date_debut;
    private string $date_fin;
    private string $motif;

    public function __construct(?int $id, string $date_debut, string $date_fin, string $motif)
    {
        $this->id = $id;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
        $this->motif = $motif;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getDateDebut(): string
    {
        return $this->date_debut;
    }
    public function getDateFin(): string
    {
        return $this->date_fin;
    }
    public function getMotif(): string
    {
        return $this->motif;
    }
}
