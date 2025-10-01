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
    public function findConflict(int $id, \DateTimeInterface $start, int $duration, string $type): bool
    {
        // Calcule heure de fin
        $endTs = $start->getTimestamp() + ($duration * 60);
        $end   = (new \DateTimeImmutable('@' . $endTs))->setTimezone($start->getTimezone());

        // Identifier la colonne (staff ou patient)
        $col = $type === 'staff' ? 'staff_id' : 'patient_id';

        $sql = "SELECT COUNT(*) FROM rdv
            WHERE $col = :id
              AND date_rdv = :date_rdv
              AND (
                  (heure_debut < :end AND heure_fin > :start)
              )";

        $request = $this->pdo->prepare($sql);
        $request->execute([
            ':id'       => $id,
            ':date_rdv' => $start->format('Y-m-d'),
            ':start'    => $start->format('H:i:s'),
            ':end'      => $end->format('H:i:s'),
        ]);

        return (bool)$request->fetchColumn();
    }

    public function generateAvailableSlots(
        string $date,
        int $duration,
        array $staffDispos,
        array $serviceDispos
    ): array {
        $slots = [];

        $dayName = strtoupper((new DateTime($date))->format('l'));
        $mapDays = [
            'MONDAY' => 'LUNDI',
            'TUESDAY' => 'MARDI',
            'WEDNESDAY' => 'MERCREDI',
            'THURSDAY' => 'JEUDI',
            'FRIDAY' => 'VENDREDI',
            'SATURDAY' => 'SAMEDI',
            'SUNDAY' => 'DIMANCHE'
        ];
        $jourSemaine = $mapDays[$dayName];

        // Filtrer sur le jour
        $staffDayDispos   = array_filter($staffDispos, fn($d) => $d->getJourSemaine() === $jourSemaine);
        $serviceDayDispos = array_filter($serviceDispos, fn($d) => $d->getJourSemaine() === $jourSemaine);

        foreach ($staffDayDispos as $s) {
            foreach ($serviceDayDispos as $d) {
                $start = max(new DateTime("$date " . $s->getStart()), new DateTime("$date " . $d->getStart()));
                $end   = min(new DateTime("$date " . $s->getEnd()),   new DateTime("$date " . $d->getEnd()));

                $current = clone $start;
                while ($current < $end) {
                    $slotEnd = (clone $current)->modify("+$duration minutes");
                    if ($slotEnd <= $end) {
                        $slots[] = [
                            'start' => clone $current,
                            'end'   => $slotEnd
                        ];
                    }
                    $current->modify("+$duration minutes");
                }
            }
        }

        return $slots;
    }
}
