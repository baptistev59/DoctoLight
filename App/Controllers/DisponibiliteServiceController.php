<?php

class DisponibiliteServiceController
{
    private DisponibiliteServiceManager $dispoManager;
    private ServiceManager $serviceManager;
    private AuthController $authController;

    public function __construct(PDO $pdo)
    {
        $this->dispoManager = new DisponibiliteServiceManager($pdo);
        $this->serviceManager = new ServiceManager($pdo);
        $this->authController = new AuthController($pdo);
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
}
