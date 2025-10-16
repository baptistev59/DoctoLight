<?php

declare(strict_types=1);

class FermetureController extends BaseController
{

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    // Liste / gestion (admin et secrétaire)
    public function list(): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);
        $fermetures = $this->fermetureManager->getAll();
        include __DIR__ . '/../Views/fermetures/list.php';
    }

    // Création
    public function store(): void
    {
        $this->authController->checkCsrfToken();
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);

        $debut = $_POST['date_debut'] ?? null;
        $fin   = $_POST['date_fin'] ?? null;
        $motif = trim($_POST['motif'] ?? '');

        if (!$debut || !$fin) {
            $_SESSION['error'] = "Les dates de fermeture sont obligatoires.";
            header("Location: index.php?page=fermetures");
            exit;
        }

        $id = $this->fermetureManager->create($debut, $fin, $motif);

        // Audit
        $this->audit(
            'fermetures',
            (int)$id,
            'INSERT',
            "Création d\'une fermeture du {$debut} au {$fin} (motif : {$motif})"
        );

        $_SESSION['success'] = "Fermeture enregistrée avec succès.";
        header("Location: index.php?page=fermetures");
        exit;
    }

    // Suppression
    public function delete(int $id): void
    {
        $this->authController->checkCsrfToken();
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);

        // Récupération pour Audit
        $fermeture = $this->fermetureManager->getById($id);

        $this->fermetureManager->delete($id);

        // Audit
        if ($fermeture) {
            $this->audit(
                'fermetures',
                $id,
                'DELETE',
                "Suppression de la fermeture du {$fermeture->getDateDebut()} au {$fermeture->getDateFin()} (motif : {$fermeture->getMotif()})"
            );
        } else {
            $this->audit('fermetures', $id, 'DELETE', "Suppression d\'une fermeture (enregistrement non retrouvé)");
        }

        $_SESSION['success'] = "Fermeture supprimée.";
        header("Location: index.php?page=fermetures");
        exit;
    }
}
