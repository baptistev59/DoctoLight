<?php

class DisponibiliteServiceController
{
    private DisponibiliteServiceManager $dispoManager;
    private ServiceManager $serviceManager;
    private AuthController $authController;
    private FermetureManager $fermetureManager;

    public function __construct(PDO $pdo)
    {
        $this->dispoManager = new DisponibiliteServiceManager($pdo);
        $this->serviceManager = new ServiceManager($pdo);
        $this->authController = new AuthController($pdo);
        $this->fermetureManager = new FermetureManager($pdo);
    }

    // Liste des disponibilités
    public function list(): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);
        $dispos = $this->dispoManager->getAllDisponibilites();
        $services = $this->serviceManager->getAllServices();
        include __DIR__ . '/../Views/disponibilites/list.php';
    }

    // Formulaire création
    public function create(): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);
        $services = $this->serviceManager->getAllServices();
        include __DIR__ . '/../Views/disponibilites/create.php';
    }

    // Enregistrer une nouvelle disponibilité
    public function store(): void
    {
        $this->authController->checkCsrfToken();

        $serviceId = intval($_POST['service_id'] ?? 0);
        $jour = strtoupper(trim($_POST['jour_semaine'] ?? ''));
        $start = new DateTime($_POST['start_time']);
        $end = new DateTime($_POST['end_time']);

        if ($start >= $end) {
            $_SESSION['error'] = "L'heure de fin doit être après celle de début.";
            header("Location: index.php?page=service_show&id=$serviceId");
            exit;
        }

        $dispo = new DisponibiliteService(null, $serviceId, $start, $end, $jour);
        $this->dispoManager->createDisponibilite($dispo);

        $_SESSION['success'] = "Disponibilité ajoutée avec succès.";
        header("Location: index.php?page=service_show&id=$serviceId");
        exit;
    }

    // Formulaire d’édition
    public function edit(int $id): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);
        $dispo = $this->dispoManager->getDisponibiliteById($id);


        if (!$dispo) {
            $_SESSION['error'] = "Disponibilité introuvable.";
            header("Location: index.php?page=dispo_services");
            exit;
        }

        // On récupère le service parent pour rediriger correctement
        $service = $this->serviceManager->getServiceById($dispo->getServiceId());
        $services = $this->serviceManager->getAllServices();

        include __DIR__ . '/../Views/disponibilites/_modal_edit.php';
    }

    // Mise à jour
    public function update(int $id): void
    {
        $this->authController->checkCsrfToken();

        $dispo = $this->dispoManager->getDisponibiliteById($id);
        if (!$dispo) {
            $_SESSION['error'] = "Disponibilité introuvable.";
            header("Location: index.php?page=services");
            exit;
        }

        $dispo->setJourSemaine($_POST['jour_semaine']);
        $dispo->setStartTime(new DateTime($_POST['start_time']));
        $dispo->setEndTime(new DateTime($_POST['end_time']));

        $this->dispoManager->updateDisponibilite($dispo);
        $_SESSION['success'] = "Disponibilité mise à jour avec succès.";
        header("Location: index.php?page=service_show&id=" . $dispo->getServiceId());
        exit;
    }

    // Suppression
    public function delete(int $id): void
    {
        $this->authController->checkCsrfToken();

        $dispo = $this->dispoManager->getDisponibiliteById($id);
        if (!$dispo) {
            $_SESSION['error'] = "Disponibilité introuvable.";
            header("Location: index.php?page=services");
            exit;
        }

        $serviceId = $dispo->getServiceId();
        $this->dispoManager->deleteDisponibilite($id);

        $_SESSION['success'] = "Disponibilité supprimée.";
        header("Location: index.php?page=service_show&id=$serviceId");
        exit;
    }

    // Calcule les horaires d'ouverture du cabinet
    public function horairesCabinet(): void
    {
        $joursOrdre = ['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE'];
        $horaires = [];

        foreach ($joursOrdre as $jour) {
            if ($this->fermetureManager->isJourFerme($jour)) {
                $horaires[$jour] = [
                    ['open' => null, 'close' => null, 'ferme' => true]
                ];
                continue;
            }

            $dispos = $this->dispoManager->getAllDisponibilitesByJour($jour);

            if (empty($dispos)) {
                $horaires[$jour] = [];
                continue;
            }

            usort($dispos, fn($a, $b) => $a->getStartTime() <=> $b->getStartTime());

            $merged = [];
            $current = [
                'start' => $dispos[0]->getStartTime(),
                'end'   => $dispos[0]->getEndTime()
            ];

            foreach ($dispos as $d) {
                $start = $d->getStartTime();
                $end   = $d->getEndTime();

                if ($start <= $current['end']) {
                    if ($end > $current['end']) {
                        $current['end'] = $end;
                    }
                } else {
                    $merged[] = $current;
                    $current = ['start' => $start, 'end' => $end];
                }
            }

            $merged[] = $current;

            $horaires[$jour] = array_map(fn($m) => [
                'open'  => $m['start']->format('H:i'),
                'close' => $m['end']->format('H:i'),
                'ferme' => false
            ], $merged);
        }

        // On charge aussi les services actifs
        $services = array_filter(
            $this->serviceManager->getAllServices(),
            fn($s) => $s->isActive()
        );

        $fermeturesActives = $this->fermetureManager->getActive();

        include __DIR__ . '/../Views/home.php';
    }


    /**
     * Calcule les horaires d'ouverture du cabinet
     * avec gestion des coupures (matin / après-midi)
     */
    private function calculerHorairesCabinet(): array
    {
        $jours = ['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE'];
        $horaires = [];

        foreach ($jours as $jour) {
            $dispos = $this->dispoManager->getAllDisponibilitesByJour($jour);

            if (empty($dispos)) {
                $horaires[$jour] = [];
                continue;
            }

            // Trie par heure de début
            usort($dispos, fn($a, $b) => $a->getStartTime() <=> $b->getStartTime());

            $merged = [];
            $current = [
                'start' => $dispos[0]->getStartTime(),
                'end'   => $dispos[0]->getEndTime()
            ];

            foreach ($dispos as $d) {
                $start = $d->getStartTime();
                $end   = $d->getEndTime();

                // Si chevauchement ou créneau continue
                if ($start <= $current['end']) {
                    if ($end > $current['end']) {
                        $current['end'] = $end;
                    }
                } else {
                    // Nouveau bloc horaire
                    $merged[] = $current;
                    $current = ['start' => $start, 'end' => $end];
                }
            }

            // Ajoute le dernier bloc
            $merged[] = $current;

            // Stocke pour ce jour
            $horaires[$jour] = array_map(fn($m) => [
                'open'  => $m['start']->format('H:i'),
                'close' => $m['end']->format('H:i')
            ], $merged);
        }

        return $horaires;
    }
}
