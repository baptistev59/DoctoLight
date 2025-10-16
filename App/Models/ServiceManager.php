<?php

declare(strict_types=1);

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

        if ($request->execute($params)) {
            $service->setId((int)$this->pdo->lastInsertId());
            return true;
        }
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
        $request = $this->pdo->prepare($sql);
        $request->execute();

        $services = [];
        while ($row = $request->fetch(PDO::FETCH_ASSOC)) {
            $services[] = new Service($row);
        }
        return $services;
    }

    public function getPaginatedServices(int $limit, int $offset): array
    {
        // Récupère les services paginés
        $request = $this->pdo->prepare("
        SELECT * FROM services 
        ORDER BY nom ASC 
        LIMIT :limit OFFSET :offset
    ");
        $request->bindValue(':limit', $limit, PDO::PARAM_INT);
        $request->bindValue(':offset', $offset, PDO::PARAM_INT);
        $request->execute();

        $services = [];
        while ($row = $request->fetch(PDO::FETCH_ASSOC)) {
            $services[] = new Service($row);
        }

        // Compte total pour pagination
        $totalrequest = $this->pdo->query("SELECT COUNT(*) FROM services");
        $totalRows = (int) $totalrequest->fetchColumn();
        $totalPages = (int) ceil($totalRows / $limit);

        return [
            'services'    => $services,
            'totalPages'  => $totalPages
        ];
    }

    public function searchServices(string $term = '', int $limit = 5, int $offset = 0): array
    {
        $likeTerm = '%' . $term . '%';

        $stmt = $this->pdo->prepare("
        SELECT * FROM services
        WHERE nom LIKE :term OR description LIKE :term
        ORDER BY nom ASC
        LIMIT :limit OFFSET :offset
    ");
        $stmt->bindValue(':term', $likeTerm, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $services = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $services[] = new Service($row);
        }

        // Compte total des résultats
        $countStmt = $this->pdo->prepare("
        SELECT COUNT(*) FROM services
        WHERE nom LIKE :term OR description LIKE :term
    ");
        $countStmt->execute([':term' => $likeTerm]);
        $totalRows = (int)$countStmt->fetchColumn();
        $totalPages = (int)ceil($totalRows / $limit);

        return [
            'services' => $services,
            'totalPages' => $totalPages,
        ];
    }

    public function getFilteredServices(
        string $search = '',
        string $sort = 'nom',
        string $order = 'ASC',
        int $limit = 5,
        int $offset = 0
    ): array {
        $allowedSort = ['nom', 'is_active'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'nom';
        }

        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $likeTerm = '%' . $search . '%';

        $stmt = $this->pdo->prepare("
        SELECT * FROM services
        WHERE nom LIKE :term OR description LIKE :term
        ORDER BY $sort $order
        LIMIT :limit OFFSET :offset
    ");
        $stmt->bindValue(':term', $likeTerm, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $services = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $services[] = new Service($row);
        }

        $countStmt = $this->pdo->prepare("
        SELECT COUNT(*) FROM services
        WHERE nom LIKE :term OR description LIKE :term
    ");
        $countStmt->execute([':term' => $likeTerm]);
        $totalRows = (int)$countStmt->fetchColumn();

        return [
            'services' => $services,
            'totalRows' => $totalRows
        ];
    }
}
