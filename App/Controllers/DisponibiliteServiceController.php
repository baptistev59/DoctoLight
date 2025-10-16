<?php

declare(strict_types=1);

class DisponibiliteServiceController extends BaseController
{

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    // Liste des disponibilités
    public function list(): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);
        $dispos = $this->dispoServiceManager->getAllDisponibilites();
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
        $this->dispoServiceManager->createDisponibilite($dispo);

        // Audit
        $this->audit('disponibilite_service', 0, 'INSERT', "Ajout d\'une disponibilité pour le service #$serviceId ($jour, {$start->format('H:i')}-{$end->format('H:i')})");

        $_SESSION['success'] = "Disponibilité ajoutée avec succès.";
        header("Location: index.php?page=service_show&id=$serviceId");
        exit;
    }

    // Formulaire d’édition
    public function edit(int $id): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);
        $dispo = $this->dispoServiceManager->getDisponibiliteById($id);


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

        $dispo = $this->dispoServiceManager->getDisponibiliteById($id);
        if (!$dispo) {
            $_SESSION['error'] = "Disponibilité introuvable.";
            header("Location: index.php?page=services");
            exit;
        }

        // Pour l'enregistrement dans l'audit
        $oldValues = "{$dispo->getJourSemaine()} {$dispo->getStartTime()->format('H:i')}-{$dispo->getEndTime()->format('H:i')}";

        $dispo->setJourSemaine($_POST['jour_semaine']);
        $dispo->setStartTime(new DateTime($_POST['start_time']));
        $dispo->setEndTime(new DateTime($_POST['end_time']));

        $this->dispoServiceManager->updateDisponibilite($dispo);

        // Audit
        $newValues = "{$dispo->getJourSemaine()} {$dispo->getStartTime()->format('H:i')}-{$dispo->getEndTime()->format('H:i')}";
        $this->audit('disponibilite_service', $id, 'UPDATE', "Modification de disponibilité ($oldValues → $newValues)");

        $_SESSION['success'] = "Disponibilité mise à jour avec succès.";
        header("Location: index.php?page=service_show&id=" . $dispo->getServiceId());
        exit;
    }

    // Suppression
    public function delete(int $id): void
    {
        $this->authController->checkCsrfToken();

        $dispo = $this->dispoServiceManager->getDisponibiliteById($id);
        if (!$dispo) {
            $_SESSION['error'] = "Disponibilité introuvable.";
            header("Location: index.php?page=services");
            exit;
        }

        $serviceId = $dispo->getServiceId();
        $this->dispoServiceManager->deleteDisponibilite($id);

        // 🧾 Audit log
        $this->audit('disponibilite_service', $id, 'DELETE', "Suppression d\'une disponibilité du service #$serviceId ({$dispo->getJourSemaine()} {$dispo->getStartTime()->format('H:i')}-{$dispo->getEndTime()->format('H:i')})");

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

            $dispos = $this->dispoServiceManager->getAllDisponibilitesByJour($jour);

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
}
