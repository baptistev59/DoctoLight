<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <h2 class="mb-4">
        <i class="bi bi-clipboard-data"></i> Journal des actions
    </h2>
    <div class="d-flex justify-content-end mb-3">
        <form method="post" action="index.php?page=auditlogs_clean" onsubmit="return confirm('Supprimer les logs de plus de 6 mois ?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button class="btn btn-outline-danger">
                <i class="bi bi-trash"></i> Nettoyer anciens logs
            </button>
        </form>
    </div>

    <form method="get" class="d-flex mb-3">
        <input type="hidden" name="page" value="auditlogs">
        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="form-control me-2" placeholder="Rechercher dans le journal...">
        <button class="btn btn-outline-primary"><i class="bi bi-search"></i> Rechercher</button>
    </form>

    <?php if (empty($logs)): ?>
        <div class="alert alert-info">Aucun enregistrement d'audit trouvé.</div>
    <?php else: ?>
        <div class="table-responsive shadow-sm">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Utilisateur</th>
                        <th scope="col">Action</th>
                        <th scope="col">Table</th>
                        <th scope="col">Description</th>
                        <th scope="col">IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['action_date']) ?></td>
                            <td>
                                <?= htmlspecialchars(trim(($log['user_prenom'] ?? '') . ' ' . ($log['user_nom'] ?? ''))) ?: '<em>Système</em>' ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= match ($log['action']) {
                                                            'INSERT' => 'success',
                                                            'UPDATE' => 'warning',
                                                            'DELETE' => 'danger',
                                                            default => 'secondary'
                                                        } ?>">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($log['table_name']) ?></td>
                            <td><?= htmlspecialchars($log['description']) ?></td>
                            <td><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>