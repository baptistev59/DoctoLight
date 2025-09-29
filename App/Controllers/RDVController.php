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

    // Liste des RDV pour un patient ou un staff
    public function listRDV(int $userId): void
    {
        $user = $this->userManager->findById($userId);
        if (!$user) {
            die("Utilisateur introuvable");
        }

        $rdvs = $user->hasRole('PATIENT')
            ? $this->rdvManager->getRdvByPatient($userId)
            : $this->rdvManager->getRdvByStaff($userId);

        view('rdv/list', ['rdvs' => $rdvs, 'user' => $user]);
    }

    // Formulaire création RDV
    public function create(): void
    {
        $currentUser = $_SESSION['user'];

        $patients = ($currentUser->hasRole('SECRETAIRE') || $currentUser->hasRole('ADMIN'))
            ? $this->userManager->getUsersByRole('PATIENT')
            : [];

        $services = $this->serviceManager->getAllServices();
        $staffs   = $this->userManager->getUsersByRole('MEDECIN');

        $availableSlots = [];
        $selectedDate = $_POST['date_rdv'] ?? null;
        $selectedServiceId = $_POST['service_id'] ?? null;
        $selectedStaffId = $_POST['staff_id'] ?? null;

        if ($selectedDate && $selectedServiceId && $selectedStaffId) {
            $service = $this->serviceManager->getServiceById((int)$selectedServiceId);
            $duration = $service->getDuree();

            $jourSemaine = strtoupper((new DateTime($selectedDate))->format('l'));
            // adapter format anglais -> français si besoin
            $mapJours = [
                'MONDAY' => 'LUNDI',
                'TUESDAY' => 'MARDI',
                'WEDNESDAY' => 'MERCREDI',
                'THURSDAY' => 'JEUDI',
                'FRIDAY' => 'VENDREDI',
                'SATURDAY' => 'SAMEDI',
                'SUNDAY' => 'DIMANCHE'
            ];
            $jourSemaine = $mapJours[$jourSemaine];

            $staffDispos   = $this->dispoStaffManager->getDisponibilitesByStaffAndDay($selectedStaffId, $jourSemaine);
            $serviceDispos = $this->dispoServiceManager->getDisponibilitesByServiceAndDay($selectedServiceId, $jourSemaine);

            $staffDayDispos = array_filter($staffDispos, fn($d) => $d->getJourSemaine() === $jourSemaine);
            $serviceDayDispos = array_filter($serviceDispos, fn($d) => $d->getJourSemaine() === $jourSemaine);


            // Croiser les dispos staff & service
            foreach ($staffDispos as $s) {
                foreach ($serviceDispos as $d) {
                    $start = max(new DateTime($s->getStart()), new DateTime($d->getStart()));
                    $end   = min(new DateTime($s->getEnd()), new DateTime($d->getEnd()));

                    while ($start->modify('+0 minutes') < $end) {
                        $slotEnd = (clone $start)->modify("+$duration minutes");
                        if ($slotEnd <= $end) {
                            $availableSlots[] = [
                                'start' => clone $start,
                                'end'   => $slotEnd
                            ];
                        }
                        $start->modify("+$duration minutes");
                    }
                }
            }
        }

        view('rdv/create', [
            'patients'          => $patients,
            'services'          => $services,
            'staffs'            => $staffs,
            'availableSlots'    => $availableSlots,
            'selectedDate'      => $selectedDate,
            'selectedServiceId' => $selectedServiceId,
            'selectedStaffId'   => $selectedStaffId,
        ]);
    }

    // Validation de la création d'un RDV après formulaire
    public function createValid(): void
    {
        $currentUser = $_SESSION['user'];
        $patientId   = $_POST['patient_id'] ?? $currentUser->getId();
        $serviceId   = $_POST['service_id'] ?? null;
        $staffId     = $_POST['staff_id'] ?? null;
        $dateRdv     = $_POST['date_rdv'] ?? null;

        if (!$serviceId || !$dateRdv || !$staffId) {
            $_SESSION['error'] = "Veuillez remplir tous les champs";
            redirect(BASE_URL . 'index.php?page=rdv_create');
        }

        $service = $this->serviceManager->getServiceById((int)$serviceId);
        if (!$service) {
            $_SESSION['error'] = "Service introuvable";
            redirect(BASE_URL . 'index.php?page=rdv_create');
        }

        $duration = $service->getDuree();

        // Récupérer les dispos du staff
        $staffDispos = $this->dispoStaffManager->getDisponibilitesByStaff($staffId);

        // Récupérer les dispos du service
        $serviceDispos = $this->dispoServiceManager->getDisponibilitesByService($serviceId);

        // Calcul des créneaux disponibles
        $availableSlots = $this->generateSlots($dateRdv, $duration, $staffDispos, $serviceDispos);

        // On recharge la vue create avec la liste des créneaux
        $patients = ($currentUser->hasRole('SECRETAIRE') || $currentUser->hasRole('ADMIN'))
            ? $this->userManager->getUsersByRole('PATIENT')
            : [];

        $services = $this->serviceManager->getAllServices();
        $staffs   = $this->userManager->getUsersByRole('MEDECIN');

        view('rdv/create', [
            'patients'         => $patients,
            'services'         => $services,
            'staffs'           => $staffs,
            'availableSlots'   => $availableSlots,
            'selectedServiceId' => $serviceId,
            'staffId'          => $staffId,
            'patientId'        => $patientId,
            'selectedDate'     => $dateRdv
        ]);
    }

    // Création des disponibilités
    private function generateSlots(string $date, int $duration, array $staffDispos, array $serviceDispos): array
    {
        $slots = [];

        $dayName = strtoupper((new DateTime($date))->format('l'));
        // format('l') = Monday, Tuesday → on doit mapper en FR
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

        // Filtrer les dispos du jour
        $staffDayDispos = array_filter($staffDispos, fn($d) => $d->getJourSemaine() === $jourSemaine);
        $serviceDayDispos = array_filter($serviceDispos, fn($d) => $d->getJourSemaine() === $jourSemaine);

        foreach ($staffDayDispos as $staffDispo) {
            foreach ($serviceDayDispos as $serviceDispo) {
                // Intersection des créneaux
                $start = max(new DateTime("$date " . $staffDispo->getStart()), new DateTime("$date " . $serviceDispo->getStart()));
                $end   = min(new DateTime("$date " . $staffDispo->getEnd()),   new DateTime("$date " . $serviceDispo->getEnd()));

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

    // Enregistrement définitif d’un RDV
    public function store(): void
    {
        $currentUser = $_SESSION['user'];
        $patientId = $_POST['patient_id'] ?? $currentUser->getId();
        $serviceId = $_POST['service_id'] ?? null;
        $staffId = $_POST['staff_id'] ?? null;
        $dateRdv = $_POST['date_rdv'] ?? null;
        $heureRdv = $_POST['heure_rdv'] ?? null;

        if (!$serviceId || !$dateRdv || !$heureRdv || !$staffId) {
            die("Veuillez remplir tous les champs");
        }

        $datetime = new \DateTime("$dateRdv $heureRdv");
        $service = $this->serviceManager->getServiceById((int)$serviceId);
        if (!$service) die("Service introuvable");

        $duration = $service->getDuree();

        // Récupérer dispos du staff et du service pour le jour sélectionné
        $dayName = strtoupper($datetime->format('l'));
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

        $staffDispos = $this->dispoStaffManager->getDisponibilitesByStaffAndDay($staffId, $jourSemaine);
        $serviceDispos = $this->dispoServiceManager->getDisponibilitesByServiceAndDay($serviceId, $jourSemaine);

        // Vérifier si le créneau est disponible
        if (!$this->isDisponible($datetime, $duration, $staffDispos)) {
            die("Le médecin n'est pas disponible à ce créneau");
        }
        if (!$this->isDisponible($datetime, $duration, $serviceDispos)) {
            die("Le service n'est pas disponible à ce créneau");
        }

        // Vérifier conflit avec le staff
        $existingStaff = $this->rdvManager->findConflict($staffId, $datetime, $duration, 'staff');
        if ($existingStaff) die("Le médecin est déjà pris sur ce créneau");

        // Vérifier conflit avec le patient
        $existingPatient = $this->rdvManager->findConflict($patientId, $datetime, $duration, 'patient');
        if ($existingPatient) die("Le patient a déjà un rendez-vous sur ce créneau");

        // Calcul de l'heure de fin
        $heureFin = (clone $datetime)->modify("+$duration minutes")->format('H:i:s');

        $rdv = new Rdv([
            'patient_id' => $patientId,
            'staff_id' => $staffId,
            'service_id' => $serviceId,
            'date_rdv' => $datetime->format('Y-m-d'),
            'heure_debut' => $datetime->format('H:i:s'),
            'heure_fin' => $heureFin,
            'statut' => 'PROGRAMME'
        ]);

        $this->rdvManager->createRdv($rdv);
        redirect(BASE_URL . 'index.php?page=rdv');
    }

    // Vérifie si un créneau est dans les disponibilités
    private function isDisponible(DateTime $start, int $duration, array $dispos): bool
    {
        $end = (clone $start)->modify("+$duration minutes");

        foreach ($dispos as $dispo) {
            $dispoStart = $dispo->getStartTime();
            $dispoEnd = $dispo->getEndTime();

            if ($start >= $dispoStart && $end <= $dispoEnd) {
                return true;
            }
        }
        return false;
    }
}
