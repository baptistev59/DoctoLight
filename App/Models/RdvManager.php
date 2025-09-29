<?php
class RdvManager
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Créer un RDV
    public function createRdv(Rdv $rdv): bool
    {
        $sql = "INSERT INTO rdv (
                    patient_id, staff_id, service_id, dispo_staff_id, dispo_service_id, 
                    date_rdv, heure_debut, heure_fin, statut
                ) VALUES (
                    :patient_id, :staff_id, :service_id, :dispo_staff_id, :dispo_service_id,
                    :date_rdv, :heure_debut, :heure_fin, :statut
                )";

        $params = [
            ':patient_id'      => $rdv->getPatientId(),
            ':staff_id'        => $rdv->getStaffId(),
            ':service_id'      => $rdv->getServiceId(),
            ':dispo_staff_id'  => $rdv->getDispoStaffId(),
            ':dispo_service_id' => $rdv->getDispoServiceId(),
            ':date_rdv'        => $rdv->getDateRdv()->format('Y-m-d'),
            ':heure_debut'     => $rdv->getHeureDebut(),
            ':heure_fin'       => $rdv->getHeureFin(),
            ':statut'          => $rdv->getStatut()
        ];

        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }

    // Récupérer un RDV par ID
    public function getRdvById(int $id): ?Rdv
    {
        $sql = "SELECT * FROM rdv WHERE id = :id";
        $request = $this->pdo->prepare($sql);
        $request->execute([':id' => $id]);
        $data = $request->fetch(PDO::FETCH_ASSOC);

        return $data ? new Rdv($data) : null;
    }

    // Récupérer les RDV par utilisateur (patient ou staff selon rôle)
    public function getRdvByUser(int $userId, array $config): array
    {
        $userManager = new UserManager($this->pdo, $config);
        $user = $userManager->findById($userId);

        if ($user && $user->hasRole('PATIENT')) {
            return $this->getRdvByPatient($userId);
        } else {
            return $this->getRdvByStaff($userId);
        }
    }

    // Récupérer tous les RDV d’un patient
    public function getRdvByPatient(int $patientId): array
    {
        $sql = "SELECT * FROM rdv WHERE patient_id = :patient_id ORDER BY date_rdv, heure_debut";
        $request = $this->pdo->prepare($sql);
        $request->execute([':patient_id' => $patientId]);

        $rdvs = [];
        while ($row = $request->fetch(PDO::FETCH_ASSOC)) {
            $rdvs[] = new Rdv($row);
        }
        return $rdvs;
    }

    // Récupérer tous les RDV d’un staff
    public function getRdvByStaff(int $staffId): array
    {
        $sql = "SELECT * FROM rdv WHERE staff_id = :staff_id ORDER BY date_rdv, heure_debut";
        $request = $this->pdo->prepare($sql);
        $request->execute([':staff_id' => $staffId]);

        $rdvs = [];
        while ($row = $request->fetch(PDO::FETCH_ASSOC)) {
            $rdvs[] = new Rdv($row);
        }
        return $rdvs;
    }

    // Mettre à jour le statut d’un RDV
    public function updateStatut(Rdv $rdv): bool
    {
        $sql = "UPDATE rdv SET statut = :statut WHERE id = :id";
        $params = [
            ':statut' => $rdv->getStatut(),
            ':id'     => $rdv->getId()
        ];
        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }

    // Mettre à jour un RDV complet
    public function updateRdv(Rdv $rdv): bool
    {
        $sql = "UPDATE rdv SET 
                    patient_id = :patient_id,
                    staff_id = :staff_id,
                    service_id = :service_id,
                    dispo_staff_id = :dispo_staff_id,
                    dispo_service_id = :dispo_service_id,
                    date_rdv = :date_rdv,
                    heure_debut = :heure_debut,
                    heure_fin = :heure_fin,
                    statut = :statut
                WHERE id = :id";

        $params = [
            ':patient_id'      => $rdv->getPatientId(),
            ':staff_id'        => $rdv->getStaffId(),
            ':service_id'      => $rdv->getServiceId(),
            ':dispo_staff_id'  => $rdv->getDispoStaffId(),
            ':dispo_service_id' => $rdv->getDispoServiceId(),
            ':date_rdv'        => $rdv->getDateRdv()->format('Y-m-d'),
            ':heure_debut'     => $rdv->getHeureDebut(),
            ':heure_fin'       => $rdv->getHeureFin(),
            ':statut'          => $rdv->getStatut(),
            ':id'              => $rdv->getId()
        ];

        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }

    // Supprimer un RDV
    public function deleteRdv(int $id): bool
    {
        $sql = "DELETE FROM rdv WHERE id = :id";
        $request = $this->pdo->prepare($sql);
        return $request->execute([':id' => $id]);
    }

    // Recherche les confilts
    public function findConflict(int $userId, \DateTime $start, int $duration, string $type = 'staff'): ?Rdv
    {
        $end = (clone $start)->modify("+$duration minutes")->format('Y-m-d H:i:s');
        $field = $type === 'staff' ? 'staff_id' : 'patient_id';

        $sql = "SELECT * FROM rdv
            WHERE $field = :user_id
              AND statut = 'PROGRAMME'
              AND date_rdv < :end
              AND DATE_ADD(date_rdv, INTERVAL duree MINUTE) > :start
            LIMIT 1";

        $request = $this->pdo->prepare($sql);
        $request->execute([
            ':user_id' => $userId,
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end
        ]);

        $row = $request->fetch(PDO::FETCH_ASSOC);
        return $row ? new Rdv($row) : null;
    }
}
