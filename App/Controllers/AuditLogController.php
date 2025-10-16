<?php

declare(strict_types=1);

class AuditLogController extends BaseController
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Liste complète des logs d’audit (ADMIN uniquement)
     */
    public function list(): void
    {
        // Sécurité : accès ADMIN seulement
        $this->authController->requireRole(['ADMIN']);

        // Récupération des logs
        $searchTerm = trim($_GET['q'] ?? '');
        $logs = $searchTerm
            ? $this->auditLogManager->search($searchTerm)
            : $this->auditLogManager->getAllWithUser();


        // Affichage
        include __DIR__ . '/../Views/auditlogs/list.php';
    }

    public function clean(): void
    {
        $this->authController->requireRole(['ADMIN']);
        $deleted = $this->auditLogManager->cleanOldLogs(6);
        $_SESSION['success'] = "$deleted entrées supprimées (plus anciennes que 6 mois)";
        header("Location: index.php?page=auditlogs");
        exit;
    }
}
