<?php

class DisponibiliteStaffManager
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Créer une disponibilité staff
    public function createDisponibilite(DisponibiliteStaff $dispo): bool
    {
        $sql = "INSERT INTO disponibilite_staff (user_id, start_time, end_time, jour_semaine)
                VALUES (:user_id, :start_time, :end_time,:jour_semaine)";
        $params = [
            ':user_id'   => $dispo->getStaffId(),
            ':start_time' => $dispo->getStartTime()->format('Y-m-d H:i:s'),
            ':end_time'   => $dispo->getEndTime()->format('Y-m-d H:i:s'),
            ':jour_semaine' => $dispo->getJourSemaine()
        ];
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Mettre à jour une disponibilité staff
    public function updateDisponibilite(DisponibiliteStaff $dispo): bool
    {
        $sql = "UPDATE disponibilite_staff 
                SET user_id = :user_id, start_time = :start_time, end_time = :end_time,jour_semaine = :jour_semaine
                WHERE id = :id";
        $params = [
            ':user_id'   => $dispo->getStaffId(),
            ':start_time' => $dispo->getStartTime(),
            ':end_time'   => $dispo->getEndTime(),
            ':id'         => $dispo->getId(),
            ':jour_semaine' => $dispo->getJourSemaine()
        ];
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Supprimer une disponibilité staff
    public function deleteDisponibilite(int $id): bool
    {
        $sql = "DELETE FROM disponibilite_staff WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Récupérer une disponibilité par ID
    public function getDisponibiliteById(int $id): ?DisponibiliteStaff
    {
        $sql = "SELECT * FROM disponibilite_staff WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new DisponibiliteStaff($row['id'], $row['user_id'], new DateTime($row['start_time']), new DateTime($row['end_time']), $row['jour_semaine']);
        }
        return null;
    }

    // Lister toutes les disponibilités staff
    public function getAllDisponibilites(): array
    {
        $sql = "SELECT * FROM disponibilite_staff";
        $stmt = $this->pdo->query($sql);
        $dispos = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dispos[] = new DisponibiliteStaff($row['id'], $row['user_id'], $row['start_time'], $row['end_time'], $row['jour_semaine']);
        }

        return $dispos;
    }

    public function getDisponibilitesByStaff(int $staffId): array
    {
        $sql = "SELECT * FROM disponibilite_staff WHERE user_id = :user_id ORDER BY start_time ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $staffId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dispos = [];
        foreach ($rows as $row) {
            $dispos[] = new DisponibiliteStaff(
                $row['id'],
                $row['user_id'],
                new DateTime($row['start_time']),
                new DateTime($row['end_time']),
                $row['jour_semaine']
            );
        }
        return $dispos;
    }

    public function getDisponibilitesByStaffAndDay(int $staffId, string $jour): array
    {
        $sql = "SELECT * FROM disponibilite_staff 
            WHERE user_id = :staffId AND jour_semaine = :jour
            ORDER BY start_time ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':staffId' => $staffId,
            ':jour'    => $jour
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // var_dump($rows);
        $dispos = [];
        foreach ($rows as $row) {
            $dispos[] = new DisponibiliteStaff(
                $row['id'],
                $row['user_id'],
                new DateTime($row['start_time']),
                new DateTime($row['end_time']),
                $row['jour_semaine']
            );
        }
        return $dispos;
    }
}
