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
        // Vérifier s'il existe déjà un RDV actif pour ce patient au même créneau
        $sqlCheckPatient = "SELECT COUNT(*) FROM rdv 
                        WHERE patient_id = :pid 
                        AND date_rdv = :date 
                        AND heure_debut = :heure 
                        AND statut != 'ANNULE'";
        $request = $this->pdo->prepare($sqlCheckPatient);
        $request->execute([
            ':pid'   => $rdv->getPatientId(),
            ':date'  => $rdv->getDateRdv()->format('Y-m-d'),
            ':heure' => $rdv->getHeureDebut()
        ]);

        if ($request->fetchColumn() > 0) {
            throw new Exception("Ce patient a déjà un RDV actif à cette heure.");
        }

        // Vérifier aussi pour le staff (médecin)
        $sqlCheckStaff = "SELECT COUNT(*) FROM rdv 
                      WHERE staff_id = :sid 
                      AND date_rdv = :date 
                      AND heure_debut = :heure 
                      AND statut != 'ANNULE'";
        $request = $this->pdo->prepare($sqlCheckStaff);
        $request->execute([
            ':sid'   => $rdv->getStaffId(),
            ':date'  => $rdv->getDateRdv()->format('Y-m-d'),
            ':heure' => $rdv->getHeureDebut()
        ]);

        if ($request->fetchColumn() > 0) {
            throw new Exception("Le médecin a déjà un RDV actif à cette heure.");
        }

        // Si tout va bien, on insère le RDV
        $sql = "INSERT INTO rdv (
                patient_id, staff_id, service_id, dispo_staff_id, dispo_service_id, 
                date_rdv, heure_debut, heure_fin, duree, statut
            ) VALUES (
                :patient_id, :staff_id, :service_id, :dispo_staff_id, :dispo_service_id,
                :date_rdv, :heure_debut, :heure_fin, :duree, :statut
            )";

        $params = [
            ':patient_id'       => $rdv->getPatientId(),
            ':staff_id'         => $rdv->getStaffId(),
            ':service_id'       => $rdv->getServiceId(),
            ':dispo_staff_id'   => $rdv->getDispoStaffId(),
            ':dispo_service_id' => $rdv->getDispoServiceId(),
            ':date_rdv'         => $rdv->getDateRdv()->format('Y-m-d'),
            ':heure_debut'      => $rdv->getHeureDebut(),
            ':heure_fin'        => $rdv->getHeureFin(),
            ':duree'            => $rdv->getDuree(),
            ':statut'           => $rdv->getStatut()
        ];

        $request = $this->pdo->prepare($sql);

        try {
            return $request->execute($params);
        } catch (PDOException $e) {
            // Capture propre si la contrainte SQL est encore violée
            if ($e->getCode() === '23000') {
                throw new Exception("Conflit détecté : un RDV existe déjà à ce créneau.");
            }
            throw $e;
        }
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

    // Récupérer tous les RDV d’un patient avec infos service + médecin
    public function getRdvByPatient(int $patientId): array
    {
        $sql = "SELECT r.*,
                   s.nom AS service_nom,
                   st.nom AS staff_nom, st.prenom AS staff_prenom
            FROM rdv r
            JOIN services s ON r.service_id = s.id
            JOIN users st ON r.staff_id = st.id
            WHERE r.patient_id = :patient_id
            ORDER BY r.date_rdv ASC, r.heure_debut ASC";

        $request = $this->pdo->prepare($sql);
        $request->execute([':patient_id' => $patientId]);

        return $request->fetchAll(PDO::FETCH_ASSOC);
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
    public function updateStatut(int $rdvId, string $statut): bool
    {
        $sql = "UPDATE rdv SET statut = :statut WHERE id = :id";
        $request = $this->pdo->prepare($sql);
        return $request->execute([
            ':statut' => $statut,
            ':id'     => $rdvId
        ]);
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
            ':patient_id'       => $rdv->getPatientId(),
            ':staff_id'         => $rdv->getStaffId(),
            ':service_id'       => $rdv->getServiceId(),
            ':dispo_staff_id'   => $rdv->getDispoStaffId(),
            ':dispo_service_id' => $rdv->getDispoServiceId(),
            ':date_rdv'         => $rdv->getDateRdv() instanceof DateTimeInterface
                ? $rdv->getDateRdv()->format('Y-m-d')
                : $rdv->getDateRdv(),
            ':heure_debut'      => $rdv->getHeureDebut(),
            ':heure_fin'        => $rdv->getHeureFin(),
            ':statut'           => $rdv->getStatut(),
            ':id'               => $rdv->getId()
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
    public function findConflict(
        int $entityId,
        DateTimeInterface $start,
        int $duration,
        string $type = 'staff',
        ?int $excludeId = null
    ): bool {
        if ($start instanceof DateTimeImmutable) {
            $start = new DateTime($start->format('Y-m-d H:i:s'));
        }

        $end = (clone $start)->modify("+{$duration} minutes");

        $column = $type === 'staff' ? 'staff_id' : 'patient_id';

        $sql = "SELECT COUNT(*) FROM rdv 
            WHERE $column = :id
            AND date_rdv = :date_rdv
            AND (heure_debut < :end AND heure_fin > :start)
            AND statut != 'ANNULE'";

        if ($excludeId) {
            $sql .= " AND id != :excludeId";
        }

        $request = $this->pdo->prepare($sql);

        $params = [
            ':id'       => $entityId,
            ':date_rdv' => $start->format('Y-m-d'),
            ':start'    => $start->format('H:i:s'),
            ':end'      => $end->format('H:i:s'),
        ];
        if ($excludeId) {
            $params[':excludeId'] = $excludeId;
        }

        $request->execute($params);

        return $request->fetchColumn() > 0;
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

        // 🔹 Récupérer les RDV existants non annulés pour la journée
        $sql = "SELECT heure_debut, heure_fin FROM rdv 
            WHERE date_rdv = :date AND statut != 'ANNULE'";
        $request = $this->pdo->prepare($sql);
        $request->execute([':date' => $date]);
        $rdvOccupes = $request->fetchAll(PDO::FETCH_ASSOC);

        foreach ($staffDayDispos as $s) {
            foreach ($serviceDayDispos as $d) {
                $start = max(new DateTime("$date " . $s->getStart()), new DateTime("$date " . $d->getStart()));
                $end   = min(new DateTime("$date " . $s->getEnd()),   new DateTime("$date " . $d->getEnd()));

                $current = clone $start;
                while ($current < $end) {
                    $slotEnd = (clone $current)->modify("+$duration minutes");
                    $isBusy = false;
                    foreach ($rdvOccupes as $rdv) {
                        if (
                            ($current->format('H:i:s') < $rdv['heure_fin']) &&
                            ($slotEnd->format('H:i:s') > $rdv['heure_debut'])
                        ) {
                            $isBusy = true;
                            break;
                        }
                    }
                    if (!$isBusy && $slotEnd <= $end) {
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
    public function getRdvForWeekDetailed(DateTimeInterface $start, DateTimeInterface $end, ?int $staffId = null, ?int $serviceId = null, ?int $patientId = null): array
    {
        $sql = "SELECT
                r.*, 
                up.nom  AS patient_nom,
                up.prenom AS patient_prenom,
                us.nom  AS staff_nom,
                us.prenom AS staff_prenom,
                s.nom   AS service_nom
            FROM rdv r
            JOIN users up ON up.id = r.patient_id
            JOIN users us ON us.id = r.staff_id
            JOIN services s ON s.id = r.service_id
            WHERE r.date_rdv BETWEEN :start AND :end";

        $params = [
            ':start' => $start->format('Y-m-d'),
            ':end' => $end->format('Y-m-d'),
        ];

        if (!empty($staffId)) {
            $sql .= " AND r.staff_id = :staff_id";
            $params[':staff_id'] = $staffId;
        }
        if (!empty($serviceId)) {
            $sql .= " AND r.service_id = :service_id";
            $params[':service_id'] = $serviceId;
        }
        if (!empty($patientId)) {
            $sql .= " AND r.patient_id = :patient_id";
            $params[':patient_id'] = $patientId;
        }

        $sql .= " ORDER BY r.date_rdv ASC, r.heure_debut ASC";

        $request = $this->pdo->prepare($sql);
        $request->execute($params);

        return $request->fetchAll(PDO::FETCH_ASSOC);
    }
}
