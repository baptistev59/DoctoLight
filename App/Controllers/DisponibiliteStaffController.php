<?php

class DisponibiliteStaffController
{
    private DisponibiliteStaffManager $dispoManager;
    private UserManager $userManager;
    private AuthController $authController;

    public function __construct(PDO $pdo)
    {
        $this->dispoManager = new DisponibiliteStaffManager($pdo);
        $this->userManager = new UserManager($pdo, []);
        $this->authController = new AuthController($pdo);
    }

    // Liste des disponibilités
    public function list(): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);
        $dispos = $this->dispoManager->getAllDisponibilites();
        $users = $this->userManager->findAll();
        include __DIR__ . '/../Views/disponibilites_staff/list.php';
    }

    // Créer une disponibilité
    public function store(): void
    {
        $this->authController->checkCsrfToken();

        $staffId = intval($_POST['user_id'] ?? 0);
        $jour = strtoupper(trim($_POST['jour_semaine'] ?? ''));
        $start = new DateTime($_POST['start_time']);
        $end = new DateTime($_POST['end_time']);

        if ($start >= $end) {
            $_SESSION['error'] = "L'heure de fin doit être après celle de début.";
            header("Location: index.php?page=profile&id={$staffId}");
            exit;
        }

        $dispo = new DisponibiliteStaff(null, $staffId, $start, $end, $jour);
        $this->dispoManager->createDisponibilite($dispo);

        $_SESSION['success'] = "Disponibilité ajoutée avec succès.";
        header("Location: index.php?page=profile&id={$staffId}");
        exit;
    }

    // Mise à jour
    public function update(int $id): void
    {
        $this->authController->checkCsrfToken();

        $dispo = $this->dispoManager->getDisponibiliteById($id);
        if (!$dispo) {
            $_SESSION['error'] = "Disponibilité introuvable.";
            header("Location: index.php?page=staff");
            exit;
        }

        $userId = intval($_POST['user_id'] ?? 0);
        $dispo->setJourSemaine($_POST['jour_semaine']);
        $dispo->setStartTime(new DateTime($_POST['start_time']));
        $dispo->setEndTime(new DateTime($_POST['end_time']));
        $dispo->setStaffId($userId);

        $this->dispoManager->updateDisponibilite($dispo);

        $_SESSION['success'] = "Disponibilité mise à jour.";
        header("Location: index.php?page=profile&id=" . $dispo->getStaffId());
        exit;
    }

    // Suppression
    public function delete(int $id): void
    {
        $this->authController->checkCsrfToken();

        $dispo = $this->dispoManager->getDisponibiliteById($id);
        if ($dispo) {
            $this->dispoManager->deleteDisponibilite($id);
            $_SESSION['success'] = "Disponibilité supprimée.";
            header("Location: index.php?page=profile&id=" . $dispo->getStaffId());
            exit;
        }
        $_SESSION['error'] = "Disponibilité introuvable.";
        header("Location: index.php?page=staff");
        exit;
    }
}
