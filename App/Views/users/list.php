<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">

    <?php
    // Variables d’origine
    $order = $_GET['order'] ?? 'ASC';
    $newOrder = $order === 'ASC' ? 'DESC' : 'ASC';
    $searchParam = urlencode($_GET['search'] ?? '');
    $sortParam = urlencode($_GET['sort'] ?? 'id');
    $currentPage = (int)($_GET['page_num'] ?? 1);

    // Rôle courant
    $roles = array_map(fn($role) => strtoupper($role->getName()), $_SESSION['user']->getRoles());
    $isAdmin = $_SESSION['user']->hasRole('ADMIN');
    ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-primary mb-0">Gestion des utilisateurs</h1>

        <?php if ($isAdmin): ?>
            <a href="<?= BASE_URL ?>index.php?page=users_create" class="btn btn-success">
                <i class="bi bi-person-plus"></i> Créer un utilisateur
            </a>
        <?php endif; ?>
    </div>

    <!-- Feedback -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']);
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']);
                                        unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Recherche -->
    <form method="GET" action="<?= BASE_URL ?>index.php" class="row g-2 mb-4">
        <input type="hidden" name="page" value="users">
        <div class="col-md-8">
            <input type="text" name="search" class="form-control" placeholder="Rechercher un utilisateur..."
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Rechercher
            </button>
        </div>
    </form>

    <!-- Tableau -->
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>
                        <a class="text-decoration-none text-dark"
                            href="<?= BASE_URL ?>index.php?page=users&sort=nom&order=<?= $newOrder ?>&search=<?= $searchParam ?>&page_num=<?= $currentPage ?>">
                            Nom
                        </a>
                    </th>
                    <th>
                        <a class="text-decoration-none text-dark"
                            href="<?= BASE_URL ?>index.php?page=users&sort=prenom&order=<?= $newOrder ?>&search=<?= $searchParam ?>&page_num=<?= $currentPage ?>">
                            Prénom
                        </a>
                    </th>
                    <th>
                        <a class="text-decoration-none text-dark"
                            href="<?= BASE_URL ?>index.php?page=users&sort=email&order=<?= $newOrder ?>&search=<?= $searchParam ?>&page_num=<?= $currentPage ?>">
                            Email
                        </a>
                    </th>
                    <th>Actif</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>index.php?page=users_view&id=<?= $user->getId() ?>"
                                    class="text-decoration-none fw-semibold">
                                    <?= htmlspecialchars($user->getNom()) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($user->getPrenom()) ?></td>
                            <td><?= htmlspecialchars($user->getEmail()) ?></td>
                            <td>
                                <?php if ($isAdmin && $user->getId() !== $_SESSION['user']->getId()): ?>
                                    <form method="POST" action="<?= BASE_URL ?>index.php?page=users_toggle&id=<?= $user->getId() ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="id" value="<?= $user->getId() ?>">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                name="is_active" <?= $user->isActive() ? 'checked' : '' ?>
                                                onchange="if(confirm('Voulez-vous vraiment <?= $user->isActive() ? 'désactiver' : 'activer' ?> cet utilisateur ?')) this.form.submit(); else this.checked = !this.checked;">
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <span class="badge <?= $user->isActive() ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $user->isActive() ? 'Oui' : 'Non' ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($isAdmin): ?>
                                    <a href="<?= BASE_URL ?>index.php?page=users_edit&id=<?= $user->getId() ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>index.php?page=users_delete&id=<?= $user->getId() ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Aucun utilisateur trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <nav aria-label="Pagination">
            <ul class="pagination justify-content-center mt-3">
                <!-- Bouton "Précédent" -->
                <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="<?= BASE_URL ?>index.php?page=users&page_num=<?= max(1, $currentPage - 1) ?>&search=<?= $searchParam ?>&sort=<?= $sortParam ?>&order=<?= $order ?>">
                        &laquo; Précédent
                    </a>
                </li>

                <!-- Pages numérotées -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                        <a class="page-link"
                            href="<?= BASE_URL ?>index.php?page=users&page_num=<?= $i ?>&search=<?= $searchParam ?>&sort=<?= $sortParam ?>&order=<?= $order ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Bouton "Suivant" -->
                <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="<?= BASE_URL ?>index.php?page=users&page_num=<?= min($totalPages, $currentPage + 1) ?>&search=<?= $searchParam ?>&sort=<?= $sortParam ?>&order=<?= $order ?>">
                        Suivant &raquo;
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>