<?php

declare(strict_types=1);

class AuditLogManager
{
    public function __construct(private PDO $pdo) {}

    public function logFromModel(AuditLog $log): void
    {
        $sql = "INSERT INTO audit_log 
                (table_name, entity_id, action, description, user_id, ip_address, action_date)
                VALUES (:table_name, :entity_id, :action, :description, :user_id, :ip, :action_date)";

        $request = $this->pdo->prepare($sql);
        $request->execute([
            ':table_name'  => $log->getTableName(),
            ':entity_id'   => $log->getEntityId(),
            ':action'      => strtoupper($log->getAction()),
            ':description' => $log->getDescription(),
            ':user_id'     => $log->getUserId(),
            ':ip'          => $log->getIpAddress(),
            ':action_date' => $log->getActionDate(),
        ]);
    }

    // Pour récupérer les logs (utile pour affichage admin)
    public function getAll(int $limit = 100): array
    {
        $request = $this->pdo->prepare("
            SELECT a.*, u.nom AS user_nom, u.prenom AS user_prenom
            FROM audit_log a
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.action_date DESC
            LIMIT :limit
        ");
        $request->bindValue(':limit', $limit, PDO::PARAM_INT);
        $request->execute();

        $rows = $request->fetchAll(PDO::FETCH_ASSOC);
        $logs = [];
        foreach ($rows as $row) {
            $logs[] = new AuditLog(
                $row['id'],
                $row['table_name'],
                $row['entity_id'],
                $row['action'],
                $row['description'],
                $row['action_date'],
                $row['user_id'],
                $row['ip_address']
            );
        }
        return $logs;
    }

    public function getAllWithUser(): array
    {
        $sql = "
        SELECT a.*, u.nom AS user_nom, u.prenom AS user_prenom
        FROM audit_log a
        LEFT JOIN users u ON u.id = a.user_id
        ORDER BY a.action_date DESC
    ";
        $request = $this->pdo->query($sql);
        return $request->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cleanOldLogs(int $months = 6): int
    {
        $sql = "DELETE FROM audit_log WHERE action_date < DATE_SUB(NOW(), INTERVAL :months MONTH)";
        $request = $this->pdo->prepare($sql);
        $request->bindValue(':months', $months, PDO::PARAM_INT);
        $request->execute();
        return $request->rowCount(); // nombre de logs supprimés
    }

    public function search(string $term): array
    {
        $sql = "
        SELECT a.*, u.nom AS user_nom, u.prenom AS user_prenom
        FROM audit_log a
        LEFT JOIN users u ON u.id = a.user_id
        WHERE a.table_name LIKE :term
           OR a.action LIKE :term
           OR a.description LIKE :term
           OR u.nom LIKE :term
           OR u.prenom LIKE :term
        ORDER BY a.action_date DESC
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':term' => "%$term%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
