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
        $request = $this->pdo->query("SELECT * FROM fermeture_exceptionnelle ORDER BY date_debut ASC");
        return $request->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?Fermeture
    {
        $request = $this->pdo->prepare("SELECT * FROM fermetures WHERE id = :id");
        $request->execute(['id' => $id]);
        $data = $request->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new Fermeture(
                $data['id'],
                $data['date_debut'],
                $data['date_fin'],
                $data['motif']
            );
        }

        return null;
    }


    /** Liste des fermetures actives (aujourd'hui ou à venir) */
    public function getActive(): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM fermeture_exceptionnelle
                WHERE date_fin >= :today
                ORDER BY date_debut ASC";
        $request = $this->pdo->prepare($sql);
        $request->execute([':today' => $today]);
        return $request->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Création d’une fermeture */
    public function create(string $dateDebut, string $dateFin, ?string $motif = null): bool
    {
        $sql = "INSERT INTO fermeture_exceptionnelle (date_debut, date_fin, motif)
                VALUES (:debut, :fin, :motif)";
        $request = $this->pdo->prepare($sql);
        return $request->execute([
            ':debut' => $dateDebut,
            ':fin'   => $dateFin,
            ':motif' => $motif
        ]);
    }

    /** Suppression d’une fermeture */
    public function delete(int $id): bool
    {
        $request = $this->pdo->prepare("DELETE FROM fermeture_exceptionnelle WHERE id = :id");
        return $request->execute([':id' => $id]);
    }

    /** Vérifie si une date donnée tombe pendant une fermeture */
    public function isDateFermee(string $date): bool
    {
        $sql = "SELECT COUNT(*) FROM fermeture_exceptionnelle
                WHERE :date BETWEEN date_debut AND date_fin";
        $request = $this->pdo->prepare($sql);
        $request->execute([':date' => $date]);
        return $request->fetchColumn() > 0;
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
