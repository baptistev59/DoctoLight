<?php
class FermetureController
{
    private FermetureManager $fermetureManager;
    private AuthController $authController;

    public function __construct(PDO $pdo)
    {
        $this->fermetureManager = new FermetureManager($pdo);
        $this->authController = new AuthController($pdo);
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

        $this->fermetureManager->create($debut, $fin, $motif);
        $_SESSION['success'] = "Fermeture enregistrée avec succès.";
        header("Location: index.php?page=fermetures");
        exit;
    }

    // Suppression
    public function delete(int $id): void
    {
        $this->authController->checkCsrfToken();
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);

        $this->fermetureManager->delete($id);
        $_SESSION['success'] = "Fermeture supprimée.";
        header("Location: index.php?page=fermetures");
        exit;
    }
}
