<?php
class FermetureManager
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM fermeture_exceptionnelle ORDER BY date_debut ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Liste des fermetures actives (aujourd'hui ou à venir) */
    public function getActive(): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM fermeture_exceptionnelle
                WHERE date_fin >= :today
                ORDER BY date_debut ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':today' => $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Création d’une fermeture */
    public function create(string $dateDebut, string $dateFin, ?string $motif = null): bool
    {
        $sql = "INSERT INTO fermeture_exceptionnelle (date_debut, date_fin, motif)
                VALUES (:debut, :fin, :motif)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':debut' => $dateDebut,
            ':fin'   => $dateFin,
            ':motif' => $motif
        ]);
    }

    /** Suppression d’une fermeture */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM fermeture_exceptionnelle WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** Vérifie si une date donnée tombe pendant une fermeture */
    public function isDateFermee(string $date): bool
    {
        $sql = "SELECT COUNT(*) FROM fermeture_exceptionnelle
                WHERE :date BETWEEN date_debut AND date_fin";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':date' => $date]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Vérifie si un jour de la semaine correspond à une fermeture planifiée
     * Exemple : 'LUNDI', 'MARDI', etc.
     */
    public function isJourFerme(string $jour): bool
    {
        // Convertit le jour (LUNDI, MARDI, etc.) en une vraie date (prochain jour de ce type)
        $jour = strtoupper($jour);
        $mapJours = [
            'LUNDI' => 'monday',
            'MARDI' => 'tuesday',
            'MERCREDI' => 'wednesday',
            'JEUDI' => 'thursday',
            'VENDREDI' => 'friday',
            'SAMEDI' => 'saturday',
            'DIMANCHE' => 'sunday'
        ];

        if (!isset($mapJours[$jour])) {
            return false; // jour non valide
        }

        // Calcule la date du prochain jour correspondant
        $dateCible = date('Y-m-d', strtotime("next " . $mapJours[$jour]));

        // Vérifie si cette date est dans une fermeture planifiée
        return $this->isDateFermee($dateCible);
    }
}
