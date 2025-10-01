<?php

class DisponibiliteServiceManager
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Créer une disponibilité de service
    public function createDisponibilite(DisponibiliteService $dispo): bool
    {
        $sql = "INSERT INTO disponibilite_service (service_id, start_time, end_time, jour_semaine)
                VALUES (:service_id, :start_time, :end_time, :jour_semaine)";
        $params = [
            ':service_id' => $dispo->getServiceId(),
            ':start_time' => $dispo->getStartTime()->format('Y-m-d H:i:s'),
            ':end_time'   => $dispo->getEndTime()->format('Y-m-d H:i:s'),
            ':jour_semaine' => $dispo->getJourSemaine()
        ];
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Mettre à jour une disponibilité de service
    public function updateDisponibilite(DisponibiliteService $dispo): bool
    {
        $sql = "UPDATE disponibilite_service 
                SET service_id = :service_id, start_time = :start_time, end_time = :end_time,jour_semaine = :jour_semaine
                WHERE id = :id";
        $params = [
            ':service_id' => $dispo->getServiceId(),
            ':start_time' => $dispo->getStartTime(),
            ':end_time'   => $dispo->getEndTime(),
            ':id'         => $dispo->getId(),
            ':jour_semaine' => $dispo->getJourSemaine()
        ];
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Supprimer une disponibilité de service
    public function deleteDisponibilite(int $id): bool
    {
        $sql = "DELETE FROM disponibilite_service WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Récupérer une disponibilité par ID
    public function getDisponibiliteById(int $id): ?DisponibiliteService
    {
        $sql = "SELECT * FROM disponibilite_service WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new DisponibiliteService($row['id'], $row['service_id'], $row['start_time'], $row['end_time'], $row['jour_semaine']);
        }
        return null;
    }

    // Lister toutes les disponibilités de service
    public function getAllDisponibilites(): array
    {
        $sql = "SELECT * FROM disponibilite_service";
        $stmt = $this->pdo->query($sql);
        $dispos = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dispos[] = new DisponibiliteService($row['id'], $row['service_id'], $row['start_time'], $row['end_time'], $row['jour_semaine']);
        }

        return $dispos;
    }

    public function getDisponibilitesByService(int $serviceId): array
    {
        $sql = "SELECT * FROM disponibilite_service WHERE service_id = :service_id ORDER BY start_time ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':service_id' => $serviceId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dispos = [];
        foreach ($rows as $row) {
            $dispos[] = new DisponibiliteService(
                $row['id'],
                $row['service_id'],
                new DateTime($row['start_time']),
                new DateTime($row['end_time']),
                $row['jour_semaine']
            );
        }
        return $dispos;
    }

    public function getDisponibilitesByServiceAndDay(int $serviceId, string $jourSemaine): array
    {
        $sql = "SELECT * FROM disponibilite_service 
            WHERE service_id = :serviceId AND jour_semaine = :jourSemaine
            ORDER BY start_time ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':serviceId' => $serviceId,
            ':jourSemaine' => $jourSemaine
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // var_dump($rows);
        $dispos = [];
        foreach ($rows as $row) {
            $dispos[] = new DisponibiliteService(
                $row['id'],
                $row['service_id'],
                new DateTime($row['start_time']),
                new DateTime($row['end_time']),
                $row['jour_semaine']
            );
        }
        return $dispos;
    }
}
