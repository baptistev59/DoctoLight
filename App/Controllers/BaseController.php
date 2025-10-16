<?php

declare(strict_types=1);

class BaseController
{
    protected ?PDO $pdo;
    protected ?AuditLogManager $auditLogManager;
    protected ?ServiceManager $serviceManager = null;
    protected ?DisponibiliteServiceManager $dispoServiceManager = null;
    protected ?NewsManager $newsManager = null;
    protected ?FermetureManager $fermetureManager = null;
    protected ?UserManager $userManager = null;
    protected ?array $config = null;
    protected ?AuthController $authController = null;
    protected ?DisponibiliteStaffManager $dispoStaffManager = null;
    protected ?RdvManager $rdvManager  = null;



    public function __construct(PDO $pdo)
    {
        // Stocker d’abord le PDO
        $this->pdo = $pdo;

        // Charger la configuration de manière sûre
        $configPath = dirname(__DIR__, 2) . '/Config/config.php';
        if (file_exists($configPath)) {
            $loaded = require $configPath;
            $this->config = is_array($loaded) ? $loaded : [];
        } else {
            $this->config = [];
        }

        // Initialiser les managers
        $this->auditLogManager = new AuditLogManager($pdo);
        $this->serviceManager = new ServiceManager($pdo);
        $this->dispoServiceManager = new DisponibiliteServiceManager($pdo);
        $this->newsManager = new NewsManager($pdo);
        $this->fermetureManager = new FermetureManager($pdo);
        $this->userManager = new UserManager($pdo, $this->config);
        $this->dispoStaffManager = new DisponibiliteStaffManager($pdo);
        $this->rdvManager = new RdvManager($pdo);

        // Créer un AuthController sauf si on est déjà dans AuthController
        if (!$this instanceof AuthController) {
            $this->authController = new AuthController($pdo, $this->config);
        }
    }

    /**
     * Retourne l'ID de l'utilisateur connecté (ou null si personne)
     */
    protected function getCurrentUserId(): ?int
    {
        if (!empty($_SESSION['user']) && method_exists($_SESSION['user'], 'getId')) {
            return (int) $_SESSION['user']->getId();
        }
        return null;
    }

    /**
     * Retourne l’adresse IP actuelle de l’utilisateur
     */
    protected function getUserIp(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     * Simplifie l'enregistrement d'une action dans le journal d'audit
     */
    protected function audit(string $table, int $entityId, string $action, ?string $description = null): void
    {
        $log = new AuditLog(
            null,
            $table,
            $entityId,
            strtoupper($action),
            $description,
            date('Y-m-d H:i:s'),
            $this->getCurrentUserId(),
            $this->getUserIp()
        );

        try {
            $this->auditLogManager->logFromModel($log);
        } catch (Throwable $e) {
            // On ignore les erreurs de log pour ne pas bloquer les actions principales
            // error_log("Audit log failed: " . $e->getMessage());
            echo "<pre style='color:red'>Erreur AuditLog : " . $e->getMessage() . "</pre>";
            throw $e;
        }
    }

    public function setAuthController(AuthController $authController): void
    {
        $this->authController = $authController;
    }
}
