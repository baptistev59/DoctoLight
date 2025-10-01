<?php

class RDVController
{
    private PDO $pdo;
    private array $config;
    private UserManager $userManager;
    private ServiceManager $serviceManager;
    private DisponibiliteStaffManager $dispoStaffManager;
    private DisponibiliteServiceManager $dispoServiceManager;
    private RdvManager $rdvManager;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;

        $this->userManager = new UserManager($pdo, $config);
        $this->serviceManager = new ServiceManager($pdo, $config);
        $this->dispoStaffManager = new DisponibiliteStaffManager($pdo);
        $this->dispoServiceManager = new DisponibiliteServiceManager($pdo);
        $this->rdvManager = new RdvManager($pdo);
    }

    public function listRDV(int $userId): void
    {
        $user = $this->userManager->findById($userId);
        if (!$user) die("Utilisateur introuvable");

        $rdvs = $user->hasRole('PATIENT')
            ? $this->rdvManager->getRdvByPatient($userId)
            : $this->rdvManager->getRdvByStaff($userId);

        view('rdv/list', ['rdvs' => $rdvs, 'user' => $user]);
    }

    // dans RDVController.php
    public function create(): void
    {
        $currentUser = $_SESSION['user'];
        // var_dump($_GET);

        // Récupération des patients si l'utilisateur est secrétaire
        $patients = $currentUser->hasRole('SECRETAIRE')
            ? $this->userManager->getUsersByRole('PATIENT')
            : [];

        $services = $this->serviceManager->getAllServices();
        $staffs   = $this->userManager->getUsersByRole('MEDECIN');

        foreach ($staffs as $staff) {
            // setDisplayName existe dans ta classe User
            $staff->setDisplayName($staff->getNom() . ' ' . $staff->getPrenom());
        }

        // Récupération des valeurs depuis $_GET pour persistance après changement de semaine
        $weekOffset        = (int)($_GET['week'] ?? 0);
        $selectedServiceId = isset($_GET['service_id']) ? (int)$_GET['service_id'] : (int)($_POST['service_id'] ?? 0);
        $selectedStaffId   = isset($_GET['staff_id'])   ? (int)$_GET['staff_id']   : (int)($_POST['staff_id'] ?? 0);
        $selectedPatientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : (int)($_POST['patient_id'] ?? 0);

        $startOfWeek = new DateTimeImmutable("monday this week +{$weekOffset} week");

        // Générer les dates de la semaine
        $datesSemaine = [];
        for ($i = 0; $i < 7; $i++) {
            $datesSemaine[] = $startOfWeek->modify("+{$i} days");
        }

        $availableSlots = [];

        $availableSlots = [];
        if ($selectedServiceId && $selectedStaffId) {
            // Générer tous les créneaux pour la semaine avec dispo/non dispo
            $availableSlots = $this->generateWeekSlots($selectedStaffId, $selectedServiceId, $datesSemaine);
        }

        // récupérer la durée du service sélectionné (sinon valeur par défaut 30)
        $dureeService = 30;
        if ($selectedServiceId) {
            $service = $this->serviceManager->getServiceById($selectedServiceId);
            if ($service) {
                $dureeService = (int)$service->getDuree();
            }
        }
        // var_dump($availableSlots);
        // die;

        view('rdv/create', [
            'patients'          => $patients,
            'services'          => $services,
            'staffs'            => $staffs,
            'availableSlots'    => $availableSlots,        // format: [ 'YYYY-mm-dd' => [ ['start'=>DateTime,'end'=>DateTime,'disponible'=>bool], ... ], ... ]
            'selectedServiceId' => $selectedServiceId,
            'selectedStaffId'   => $selectedStaffId,
            'selectedPatientId' => $selectedPatientId,
            'datesSemaine'      => $datesSemaine,
            'weekOffset'        => $weekOffset,
            'dureeService'      => $dureeService
        ]);
    }



    public function createValid(): void
    {
        $currentUser = $_SESSION['user'];
        $patientId   = $_POST['patient_id'] ?? $currentUser->getId();
        $serviceId   = $_POST['service_id'] ?? null;
        $staffId     = $_POST['staff_id'] ?? null;
        $dateRdv     = $_POST['date_rdv'] ?? null;

        if (!$serviceId || !$dateRdv || !$staffId) {
            $_SESSION['error'] = "Veuillez remplir tous les champs";
            redirect(BASE_URL . 'index.php?page=create_rdv');
        }

        $service = $this->serviceManager->getServiceById((int)$serviceId);
        if (!$service) {
            $_SESSION['error'] = "Service introuvable";
            redirect(BASE_URL . 'index.php?page=create_rdv');
        }

        $duration = $service->getDuree();

        $staffDispos   = $this->dispoStaffManager->getDisponibilitesByStaff($staffId);
        $serviceDispos = $this->dispoServiceManager->getDisponibilitesByService($serviceId);

        $availableSlots = $this->generateWeekSlots($dateRdv, $duration, $staffDispos, $serviceDispos);

        $patients = $currentUser->hasRole('SECRETAIRE')
            ? $this->userManager->getUsersByRole('PATIENT')
            : [];
        $services = $this->serviceManager->getAllServices();
        $staffs   = $this->userManager->getUsersByRole('MEDECIN');

        view('rdv/create', [
            'patients'          => $patients,
            'services'          => $services,
            'staffs'            => $staffs,
            'availableSlots'    => $availableSlots,
            'selectedServiceId' => $serviceId,
            'selectedStaffId'   => $staffId,
            'patientId'         => $patientId,
            'selectedDate'      => $dateRdv
        ]);
    }

    private function generateSlots(string $date, int $duration, array $staffDispos, array $serviceDispos): array
    {
        $slots = [];

        // On part du début de journée (08:00 par ex) jusqu’à la fin (18:00 par ex)
        // → tu pourras ajuster selon ton besoin (ou récupérer depuis la config/service)
        $dayStart = new DateTime($date . ' 08:00');
        $dayEnd   = new DateTime($date . ' 18:00');

        $current = clone $dayStart;

        while ($current < $dayEnd) {
            $end = (clone $current)->modify("+{$duration} minutes");

            // Vérifie si créneau dispo avec intersection Staff + Service
            $isFree = $this->isDisponible($current, $duration, $staffDispos, $serviceDispos);

            $slots[] = [
                'start'   => clone $current,
                'end'     => clone $end,
                'free'    => $isFree
            ];

            // On avance au créneau suivant
            $current->modify("+{$duration} minutes");
        }

        return $slots;
    }


    public function store(): void
    {
        $currentUser = $_SESSION['user'];

        $patientId = $_POST['patient_id'] ?? $currentUser->getId();
        $serviceId = $_POST['service_id'] ?? null;
        $staffId   = $_POST['staff_id'] ?? null;
        $dateRdv   = $_POST['date_rdv'] ?? null;
        $heureRdv  = $_POST['heure_rdv'] ?? null;

        if ($currentUser->hasRole('ADMIN')) {
            $_SESSION['error'] = "Un administrateur ne peut pas prendre de rendez-vous.";
            redirect(BASE_URL . 'index.php?page=create_rdv');
        }

        if (!$serviceId || !$dateRdv || !$heureRdv || !$staffId) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            redirect(BASE_URL . 'index.php?page=create_rdv');
        }

        $start = new DateTime("$dateRdv $heureRdv");

        // Service pour récupérer la durée
        $service = $this->serviceManager->getServiceById((int)$serviceId);
        if (!$service) {
            $_SESSION['error'] = "Service introuvable.";
            redirect(BASE_URL . 'index.php?page=create_rdv');
        }

        $duration = $service->getDuree();
        $end = (clone $start)->modify("+{$duration} minutes");
        $jourSemaine = DateHelper::getJourSemaineFR($dateRdv);

        // Vérifier dispo staff et service
        $staffDispos   = $this->dispoStaffManager->getDisponibilitesByStaffAndDay($staffId, $jourSemaine);
        $serviceDispos = $this->dispoServiceManager->getDisponibilitesByServiceAndDay($serviceId, $jourSemaine);

        if (!$this->isDisponible($start, $duration, $staffDispos, $serviceDispos)) {
            $_SESSION['error'] = "Le créneau n'est pas disponible (médecin ou service indisponible).";
            redirect(BASE_URL . 'index.php?page=create_rdv');
        }

        // Vérifier les conflits avec d'autres RDV
        if ($this->rdvManager->findConflict($staffId, $start, $duration, 'staff')) {
            $_SESSION['error'] = "Le médecin est déjà pris sur ce créneau.";
            redirect(BASE_URL . 'index.php?page=create_rdv');
        }

        if ($this->rdvManager->findConflict($patientId, $start, $duration, 'patient')) {
            $_SESSION['error'] = "Le patient a déjà un rendez-vous sur ce créneau.";
            redirect(BASE_URL . 'index.php?page=create_rdv');
        }

        // Création du RDV
        $rdv = new Rdv([
            'patient_id'  => $patientId,
            'staff_id'    => $staffId,
            'service_id'  => $serviceId,
            'date_rdv'    => $start->format('Y-m-d'),
            'heure_debut' => $start->format('H:i:s'),
            'heure_fin'   => $end->format('H:i:s'),
            'statut'      => 'PROGRAMME'
        ]);

        $this->rdvManager->createRdv($rdv);

        $_SESSION['success'] = "Rendez-vous créé avec succès.";
        redirect(BASE_URL . 'index.php?page=rdv');
    }


    private function isDisponible(DateTime $start, int $duration, array $staffDispos, array $serviceDispos): bool
    {
        $end = clone $start;
        $end->modify("+{$duration} minutes");

        $jour = $start->format('Y-m-d'); // vrai jour de la semaine testée

        $staffOk = false;
        foreach ($staffDispos as $dispo) {
            $dispoStart = (clone $start)->setTime(
                (int)$dispo->getStartTime()->format('H'),
                (int)$dispo->getStartTime()->format('i')
            );
            $dispoEnd = (clone $start)->setTime(
                (int)$dispo->getEndTime()->format('H'),
                (int)$dispo->getEndTime()->format('i')
            );

            error_log("  [Staff dispo ajusté] {$dispoStart->format('Y-m-d H:i')} -> {$dispoEnd->format('H:i')}");

            if ($start >= $dispoStart && $end <= $dispoEnd) {
                $staffOk = true;
                break;
            }
        }

        if (!$staffOk) return false;

        foreach ($serviceDispos as $dispo) {
            $dispoStart = (clone $start)->setTime(
                (int)$dispo->getStartTime()->format('H'),
                (int)$dispo->getStartTime()->format('i')
            );
            $dispoEnd = (clone $start)->setTime(
                (int)$dispo->getEndTime()->format('H'),
                (int)$dispo->getEndTime()->format('i')
            );

            error_log("  [Service dispo ajusté] {$dispoStart->format('Y-m-d H:i')} -> {$dispoEnd->format('H:i')}");

            if ($start >= $dispoStart && $end <= $dispoEnd) {
                return true;
            }
        }

        return false;
    }



    // pour test de construction de la semaine et le renvoie du résultat de generateWeekSlots
    public function debugWeekSlots(int $staffId, int $serviceId, int $weekOffset = 0): array
    {
        $startOfWeek = new DateTimeImmutable("monday this week +{$weekOffset} week");
        $datesSemaine = [];
        for ($i = 0; $i < 7; $i++) {
            $datesSemaine[] = $startOfWeek->modify("+{$i} days");
        }
        return $this->generateWeekSlots($staffId, $serviceId, $datesSemaine);
    }


    // Pour test de la fonction
    private function generateWeekSlots(int $staffId, int $serviceId, array $datesSemaine): array
    {
        $service = $this->serviceManager->getServiceById($serviceId);
        $duration = $service->getDuree();

        $allSlots = []; // $allSlots[heure][jour] = ['start'=>, 'end'=>, 'disponible'=>]

        foreach ($datesSemaine as $date) {
            $jourSemaine = DateHelper::getJourSemaineFR($date->format('Y-m-d'));

            // Récupère dispos staff et service uniquement pour ce jour
            $staffDispos   = $this->dispoStaffManager->getDisponibilitesByStaffAndDay($staffId, $jourSemaine);
            error_log("[DEBUG DISPONIBILITE STAFF] Staff=$staffId Jour=$jourSemaine Count=" . count($staffDispos));
            foreach ($staffDispos as $d) {
                error_log("  -> " . $d->getJourSemaine() . " " . $d->getStartTime()->format('H:i') . " - " . $d->getEndTime()->format('H:i'));
            }

            $serviceDispos = $this->dispoServiceManager->getDisponibilitesByServiceAndDay($serviceId, $jourSemaine);
            error_log("[DEBUG DISPONIBILITE SERVICE] Service=$serviceId Jour=$jourSemaine Count=" . count($serviceDispos));
            foreach ($serviceDispos as $d) {
                error_log("  -> " . $d->getJourSemaine() . " " . $d->getStartTime()->format('H:i') . " - " . $d->getEndTime()->format('H:i'));
            }


            // Regrouper les dispos de service par jour
            $serviceDaySlots = [];
            foreach ($serviceDispos as $sDispo) {
                $serviceDaySlots[] = [
                    'start' => (clone $date)->setTime(
                        (int)$sDispo->getStartTime()->format('H'),
                        (int)$sDispo->getStartTime()->format('i')
                    ),
                    'end'   => (clone $date)->setTime(
                        (int)$sDispo->getEndTime()->format('H'),
                        (int)$sDispo->getEndTime()->format('i')
                    )
                ];
            }

            // Ensuite pour chaque créneau unique
            foreach ($serviceDaySlots as $slot) {
                $current = clone $slot['start'];
                while ($current < $slot['end']) {
                    $slotEnd = (clone $current)->modify("+$duration minutes");

                    // Vérif dispo STAFF
                    $isStaffOk = false;
                    foreach ($staffDispos as $stDispo) {
                        $stStart = (clone $date)->setTime(
                            (int)$stDispo->getStartTime()->format('H'),
                            (int)$stDispo->getStartTime()->format('i')
                        );
                        $stEnd = (clone $date)->setTime(
                            (int)$stDispo->getEndTime()->format('H'),
                            (int)$stDispo->getEndTime()->format('i')
                        );

                        if ($current >= $stStart && $slotEnd <= $stEnd) {
                            $isStaffOk = true;
                            break;
                        }
                    }

                    // Vérif conflit RDV existant
                    $isFree = $isStaffOk && !$this->rdvManager->findConflict($staffId, $current, $duration, 'staff');

                    // log debug
                    error_log("[generateWeekSlots] $jourSemaine {$current->format('H:i')} -> {$slotEnd->format('H:i')} | StaffOk=" . ($isStaffOk ? 'YES' : 'NO') . " | Free=" . ($isFree ? 'YES' : 'NO'));

                    $allSlots[$current->format('H:i')][$date->format('Y-m-d')] = [
                        'start'      => clone $current,
                        'end'        => $slotEnd,
                        'disponible' => $isFree
                    ];

                    $current = $current->modify("+$duration minutes");
                }
            }
        }

        ksort($allSlots);
        return $allSlots;
    }

    public function planning(): void
    {
        // --- Filtres (optionnels) ---
        $selectedStaffId   = isset($_GET['staff_id'])   && $_GET['staff_id']   !== '' ? (int)$_GET['staff_id']   : null;
        $selectedServiceId = isset($_GET['service_id']) && $_GET['service_id'] !== '' ? (int)$_GET['service_id'] : null;
        $selectedPatientId = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? (int)$_GET['patient_id'] : null;
        $weekOffset        = (int)($_GET['week'] ?? 0);

        // --- Semaine courante (lundi -> dimanche) ---
        $startOfWeek = new DateTimeImmutable("monday this week +{$weekOffset} week");
        $endOfWeek   = $startOfWeek->modify('+6 days');
        $datesSemaine = [];
        for ($i = 0; $i < 7; $i++) {
            $datesSemaine[] = $startOfWeek->modify("+{$i} days");
        }

        // --- Données pour les listes ---
        $services = $this->serviceManager->getAllServices();
        $staffs   = $this->userManager->getUsersByRole('MEDECIN');
        $patients = $this->userManager->getUsersByRole('PATIENT');

        // --- RDV de la semaine (détaillés) ---
        $events = $this->rdvManager->getRdvForWeekDetailed($startOfWeek, $endOfWeek, $selectedStaffId, $selectedServiceId, $selectedPatientId);

        // --- Grille heures x jours (pas = 30 min 08:00 → 18:00) ---
        $stepMinutes = 30;
        $dayStart = new DateTimeImmutable($startOfWeek->format('Y-m-d') . ' 08:00:00');
        $dayEnd   = new DateTimeImmutable($startOfWeek->format('Y-m-d') . ' 18:00:00');

        $creneaux = []; // $creneaux['HH:MM']['YYYY-mm-dd'] = [events...]
        // Initialise toutes les cases à vide
        $times = [];
        $cursor = $dayStart;
        while ($cursor < $dayEnd) {
            $times[] = $cursor->format('H:i');
            $cursor = $cursor->modify("+{$stepMinutes} minutes");
        }
        foreach ($times as $h) {
            foreach ($datesSemaine as $d) {
                $creneaux[$h][$d->format('Y-m-d')] = [];
            }
        }

        // Place les RDV dans les cases correspondant à leur heure de début
        foreach ($events as $e) {
            // $e['date_rdv'] au format Y-m-d, $e['heure_debut'] TIME
            $hKey = substr($e['heure_debut'], 0, 5); // 'HH:MM'
            if (isset($creneaux[$hKey][$e['date_rdv']])) {
                $creneaux[$hKey][$e['date_rdv']][] = $e;
            }
        }

        // Envoi à la vue
        view('rdv/list', [
            'datesSemaine'      => $datesSemaine,
            'creneaux'          => $creneaux,
            'weekOffset'        => $weekOffset,
            'services'          => $services,
            'staffs'            => $staffs,
            'patients'          => $patients,
            'selectedServiceId' => $selectedServiceId,
            'selectedStaffId'   => $selectedStaffId,
            'selectedPatientId' => $selectedPatientId
        ]);
    }
}
