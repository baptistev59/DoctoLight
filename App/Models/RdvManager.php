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
    public function findConflict(int $entityId, DateTimeInterface $start, int $duration, string $type = 'staff'): bool
    {
        if ($start instanceof DateTimeImmutable) {
            $start = new DateTime($start->format('Y-m-d H:i:s'));
        }

        $end = (clone $start)->modify("+{$duration} minutes");

        if ($type === 'staff') {
            // Vérifie si le médecin a déjà un RDV sur ce créneau (tous services confondus)
            $sql = "SELECT COUNT(*) FROM rdv 
                WHERE staff_id = :id
                AND (
                    (date_rdv = :date_rdv)
                    AND (
                        (heure_debut < :end AND heure_fin > :start)
                    )
                )";
        } elseif ($type === 'patient') {
            // Vérifie si le patient a déjà un RDV sur ce créneau
            $sql = "SELECT COUNT(*) FROM rdv 
                WHERE patient_id = :id
                AND (
                    (date_rdv = :date_rdv)
                    AND (
                        (heure_debut < :end AND heure_fin > :start)
                    )
                )";
        } else {
            throw new InvalidArgumentException("Type $type invalide pour findConflict()");
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'       => $entityId,
            ':date_rdv' => $start->format('Y-m-d'),
            ':start'    => $start->format('H:i:s'),
            ':end'      => $end->format('H:i:s'),
        ]);

        return $stmt->fetchColumn() > 0;
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


    // RDV de la semaine avec détails (noms patient / staff / service).
    public function getRdvForWeekDetailed(DateTimeInterface $start, DateTimeInterface $end, ?int $staffId, ?int $serviceId, ?int $patientId): array
    {
        $sql = "SELECT
                r.id, r.patient_id, r.staff_id, r.service_id,
                r.date_rdv, r.heure_debut, r.heure_fin, r.statut,
                up.nom  AS patient_nom,
                up.prenom AS patient_prenom,
                us.nom  AS staff_nom,
                us.prenom AS staff_prenom,
                s.nom   AS service_nom
            FROM rdv r
            JOIN users up ON up.id = r.patient_id
            JOIN users us ON us.id = r.staff_id
            JOIN services s ON s.id = r.service_id
            WHERE r.date_rdv BETWEEN :d1 AND :d2";

        $params = [
            ':d1' => $start->format('Y-m-d'),
            ':d2' => $end->format('Y-m-d'),
        ];

        if ($staffId !== null) {
            $sql .= " AND r.staff_id = :staff_id";
            $params[':staff_id'] = $staffId;
        }
        if ($serviceId !== null) {
            $sql .= " AND r.service_id = :service_id";
            $params[':service_id'] = $serviceId;
        }
        if ($patientId !== null) {
            $sql .= " AND r.patient_id = :patient_id";
            $params[':patient_id'] = $patientId;
        }

        $sql .= " ORDER BY r.date_rdv ASC, r.heure_debut ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
