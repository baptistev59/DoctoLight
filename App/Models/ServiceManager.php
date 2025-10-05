<?php
class ServiceManager
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Récupérer tous les services
    public function getAllServices(): array
    {
        $sql = "SELECT * FROM services ORDER BY nom";
        $request = $this->pdo->prepare($sql);
        $request->execute();

        $services = [];
        while ($row = $request->fetch(PDO::FETCH_ASSOC)) {
            $services[] = new Service($row);
        }
        return $services;
    }

    // Récupérer un service par ID
    public function getServiceById(int $id): ?Service
    {
        $sql = "SELECT * FROM services WHERE id = :id";
        $request = $this->pdo->prepare($sql);
        $request->execute([':id' => $id]);
        $data = $request->fetch(PDO::FETCH_ASSOC);

        return $data ? new Service($data) : null;
    }

    // Créer un service
    public function createService(Service $service): bool
    {
        $sql = "INSERT INTO services (nom, duree, description, image, is_active)
                VALUES (:nom, :duree, :description, :image, :is_active)";
        $params = [
            ':nom'         => $service->getNom(),
            ':duree'       => $service->getDuree(),
            ':description' => $service->getDescription(),
            ':image'       => $service->getImage(),
            ':is_active'   => $service->isActive() ? 1 : 0,
        ];

        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }

    // Mettre à jour un service
    public function updateService(Service $service): bool
    {
        $sql = "UPDATE services
                SET nom = :nom,
                    duree = :duree,
                    description = :description,
                    image = :image,
                    is_active = :is_active
                WHERE id = :id";
        $params = [
            ':nom'         => $service->getNom(),
            ':duree'       => $service->getDuree(),
            ':description' => $service->getDescription(),
            ':image'       => $service->getImage(),
            ':is_active'   => $service->isActive() ? 1 : 0,
            ':id'          => $service->getId(),
        ];
        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }

    // Supprimer un service
    public function deleteService(int $id): bool
    {
        $sql = "DELETE FROM services WHERE id = :id";
        $request = $this->pdo->prepare($sql);
        return $request->execute([':id' => $id]);
    }

    // Récupérer uniquement les services actifs (utile pour la prise de RDV)
    public function getActiveServices(): array
    {
        $sql = "SELECT * FROM services WHERE is_active = 1 ORDER BY nom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $services = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $services[] = new Service($row);
        }
        return $services;
    }
}
