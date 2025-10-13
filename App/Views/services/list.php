<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
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
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary mb-0 border-bottom pb-2">Gestion des Services</h2>
            <?php if ($_SESSION['user']->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
                <a href="<?= BASE_URL ?>index.php?page=service_create" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Nouveau service
                </a>
            <?php endif; ?>
        </div>

        <!-- Champ de recherche -->
        <form method="get" action="index.php" class="mb-4 d-flex gap-2">
            <input type="hidden" name="page" value="services">
            <input type="text" name="search"
                class="form-control"
                placeholder="Rechercher un service..."
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search"></i> Rechercher
            </button>
            <?php if (!empty($_GET['search'])): ?>
                <a href="index.php?page=services" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Réinitialiser
                </a>
            <?php endif; ?>
        </form>

        <?php if (!empty($services)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle shadow-sm">
                    <thead class="table-light">
                        <tr>
                            <?php
                            $currentSort = $_GET['sort'] ?? 'nom';
                            $currentOrder = strtoupper($_GET['order'] ?? 'ASC');
                            $newOrder = $currentOrder === 'ASC' ? 'DESC' : 'ASC';
                            ?>

                            <th><a href="index.php?page=services&sort=nom&order=<?= $newOrder ?>&search=<?= urlencode($_GET['search'] ?? '') ?>"
                                    class="text-decoration-none text-dark">
                                    Nom <?= ($currentSort === 'nom') ? ($currentOrder === 'ASC' ? '↑' : '↓') : '' ?>
                                </a></th>
                            <th>Durée</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th> <a href="index.php?page=services&sort=is_active&order=<?= $newOrder ?>&search=<?= urlencode($_GET['search'] ?? '') ?>"
                                    class="text-decoration-none text-dark">
                                    Actif <?= ($currentSort === 'is_active') ? ($currentOrder === 'ASC' ? '↑' : '↓') : '' ?>
                                </a></th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td class="fw-semibold"><a href="index.php?page=service_show&id=<?= $service->getId() ?>"
                                        class="text-decoration-none fw-semibold">
                                        <?= htmlspecialchars($service->getNom()) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($service->getDuree()); ?> min</td>
                                <td><?= nl2br(substr($service->getDescription(), 0, 60)); ?><?= strlen($service->getDescription()) > 60 ? '...' : ''; ?></td>
                                <td>
                                    <?php if ($service->getImage()): ?>
                                        <img src="<?= BASE_URL ?>uploads/services/<?= htmlspecialchars($service->getImage()); ?>"
                                            alt="<?= htmlspecialchars($service->getImage()); ?>"
                                            class="img-thumbnail object-fit-cover">
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
                                    <div class="btn-group" role="group" aria-label="Actions service">
                                        <?php if ($_SESSION['user']->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
                                            <a href="index.php?page=services_edit&id=<?= $service->getId(); ?>"
                                                class="btn btn-sm btn-warning"
                                                title="Modifier">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <button type="button"
                                                class="btn btn-sm btn-danger"
                                                title="Supprimer"
                                                onclick="if (confirm('Voulez-vous vraiment supprimer ce service ?')) document.getElementById('deleteServiceForm<?= $service->getId(); ?>').submit();">
                                                <i class="bi bi-trash"></i>
                                            </button>

                                            <form id="deleteServiceForm<?= $service->getId(); ?>"
                                                method="post"
                                                action="index.php?page=services_delete&id=<?= $service->getId(); ?>"
                                                style="display:none;">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            </form>
                                        <?php endif; ?>

                                        <a href="index.php?page=create_rdv&service_id=<?= $service->getId(); ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Prendre rendez-vous">
                                            <i class="bi bi-calendar-plus"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Bootstrap -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Pagination des services">
                    <ul class="pagination justify-content-center mt-4">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == ($_GET['page_num'] ?? 1)) ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?page=services&page_num=<?= $i; ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">Aucun service n'a encore été enregistré.</div>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>