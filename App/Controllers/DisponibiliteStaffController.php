<?php

declare(strict_types=1);

class DisponibiliteStaffController extends BaseController
{

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    // Liste des disponibilités (admin et secrétaire uniquement)
    public function list(): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);
        $dispos = $this->dispoServiceManager->getAllDisponibilites();
        $users = $this->userManager->findAll();
        include __DIR__ . '/../Views/disponibilites_staff/list.php';
    }

    // Créer une disponibilité
    public function store(): void
    {
        $this->authController->checkCsrfToken();

        $currentUser = $_SESSION['user'] ?? null;
        if (!$currentUser) {
            $_SESSION['error'] = "Session expirée.";
            header("Location: index.php?page=login");
            exit;
        }

        $staffId = intval($_POST['user_id'] ?? 0);
        $jour = strtoupper(trim($_POST['jour_semaine'] ?? ''));
        $start = new DateTime($_POST['start_time']);
        $end = new DateTime($_POST['end_time']);

        // Le médecin ne peut créer que ses propres disponibilités
        if ($currentUser->hasRole('MEDECIN') && $currentUser->getId() !== $staffId) {
            $_SESSION['error'] = "Action non autorisée.";
            header("Location: index.php?page=profile");
            exit;
        }

        if ($start >= $end) {
            $_SESSION['error'] = "L'heure de fin doit être après celle de début.";
            header("Location: index.php?page=profile&id={$staffId}");
            exit;
        }

        $dispo = new DisponibiliteStaff(null, $staffId, $start, $end, $jour);
        $this->dispoStaffManager->createDisponibilite($dispo);

        // Audit
        $this->audit(
            'disponibilite_staff',
            0,
            'INSERT',
            "Ajout d'une disponibilité pour le staff #$staffId ($jour, {$start->format('H:i')}-{$end->format('H:i')})"
        );

        $_SESSION['success'] = "Disponibilité ajoutée avec succès.";
        header("Location: index.php?page=profile&id={$staffId}");
        exit;
    }

    // Mise à jour
    public function update(int $id): void
    {
        $this->authController->checkCsrfToken();

        $currentUser = $_SESSION['user'] ?? null;
        $dispo = $this->dispoStaffManager->getDisponibiliteById($id);

        if (!$dispo) {
            $_SESSION['error'] = "Disponibilité introuvable.";
            header("Location: index.php?page=staff");
            exit;
        }

        // Le médecin ne peut modifier que ses propres disponibilités
        if ($currentUser->hasRole('MEDECIN') && $currentUser->getId() !== $dispo->getStaffId()) {
            $_SESSION['error'] = "Action non autorisée.";
            header("Location: index.php?page=profile");
            exit;
        }

        $jour = strtoupper(trim($_POST['jour_semaine'] ?? ''));
        $start = new DateTime($_POST['start_time']);
        $end = new DateTime($_POST['end_time']);

        if ($start >= $end) {
            $_SESSION['error'] = "L'heure de fin doit être après celle de début.";
            header("Location: index.php?page=profile&id=" . $dispo->getStaffId());
            exit;
        }

        // Récup pour l'audit
        $oldValues = "{$dispo->getJourSemaine()} {$dispo->getStartTime()->format('H:i')}-{$dispo->getEndTime()->format('H:i')}";


        $userId = intval($_POST['user_id'] ?? 0);
        $dispo->setJourSemaine($jour);
        $dispo->setStartTime($start);
        $dispo->setEndTime($end);
        $dispo->setStaffId($userId);

        $this->dispoStaffManager->updateDisponibilite($dispo);

        // Audit
        $newValues = "{$jour} {$start->format('H:i')}-{$end->format('H:i')}";
        $this->audit(
            'disponibilite_staff',
            $id,
            'UPDATE',
            "Modification de disponibilité pour le staff #$userId ($oldValues → $newValues)"
        );

        $_SESSION['success'] = "Disponibilité mise à jour.";
        header("Location: index.php?page=profile&id=" . $dispo->getStaffId());
        exit;
    }

    // Suppression
    public function delete(int $id): void
    {
        $this->authController->checkCsrfToken();

        $currentUser = $_SESSION['user'] ?? null;
        $dispo = $this->dispoStaffManager->getDisponibiliteById($id);

        if (!$dispo) {
            $_SESSION['error'] = "Disponibilité introuvable.";
            header("Location: index.php?page=staff");
            exit;
        }

        // Le médecin ne peut supprimer que ses propres disponibilités
        if ($currentUser->hasRole('MEDECIN') && $currentUser->getId() !== $dispo->getStaffId()) {
            $_SESSION['error'] = "Action non autorisée.";
            header("Location: index.php?page=profile");
            exit;
        }

        $this->dispoStaffManager->deleteDisponibilite($id);

        // Audit
        $this->audit(
            'disponibilite_staff',
            $id,
            'DELETE',
            "Suppression d'une disponibilité du staff #{$dispo->getStaffId()} ({$dispo->getJourSemaine()} {$dispo->getStartTime()->format('H:i')}-{$dispo->getEndTime()->format('H:i')})"
        );

        $_SESSION['success'] = "Disponibilité supprimée avec succès.";
        header("Location: index.php?page=profile&id=" . $dispo->getStaffId());
        exit;
    }
}
