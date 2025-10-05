<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary mb-0 border-bottom pb-2">Gestion des Services</h2>
        <?php if ($_SESSION['user']->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
            <a href="<?= BASE_URL ?>index.php?page=services_create" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Nouveau service
            </a>
        <?php endif; ?>
    </div>

    <!-- Messages de feedback -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($services)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle shadow-sm">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Durée</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Actif</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($service->getNom()); ?></td>
                            <td><?= htmlspecialchars($service->getDuree()); ?> min</td>
                            <td><?= nl2br(htmlspecialchars(substr($service->getDescription(), 0, 60))); ?><?= strlen($service->getDescription()) > 60 ? '...' : ''; ?></td>
                            <td>
                                <?php if ($service->getImage()): ?>
                                    <img src="<?= BASE_URL ?>uploads/services/<?= htmlspecialchars($service->getImage()); ?>"
                                        alt="Image du service"
                                        class="rounded" style="width: 70px; height: 70px; object-fit: cover;">
                                <?php else: ?>
                                    <span class="text-muted">Aucune</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($_SESSION['user']->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
                                    <form method="post" action="index.php?page=services_toggle&id=<?= $service->getId(); ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                name="is_active"
                                                <?= $service->isActive() ? 'checked' : ''; ?>
                                                onchange="this.form.submit();">
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <span class="badge <?= $service->isActive() ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?= $service->isActive() ? 'Oui' : 'Non'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <?php if ($_SESSION['user']->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
                                        <a href="index.php?page=services_edit&id=<?= $service->getId(); ?>"
                                            class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="post" action="index.php?page=services_delete&id=<?= $service->getId(); ?>"
                                            onsubmit="return confirm('Voulez-vous vraiment supprimer ce service ?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="index.php?page=create_rdv&service_id=<?= $service->getId(); ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-calendar-plus"></i> RDV
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Aucun service n'a encore été enregistré.</div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>