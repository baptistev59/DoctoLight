<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-primary mb-0">Actualités</h1>

        <?php if ($authController->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
            <a href="index.php?page=create-news" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Créer une actualité
            </a>
        <?php endif; ?>
    </div>

    <!-- Messages de succès / erreur -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($newsWithAuthors)): ?>
        <div class="list-group shadow-sm">
            <?php foreach ($newsWithAuthors as $item): ?>
                <?php $news = $item['news']; ?>
                <?php $author = $item['author']; ?>

                <div class="list-group-item list-group-item-action mb-2 rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-1">
                            <a href="index.php?page=news_show&id=<?= $news->getId(); ?>"
                                class="text-decoration-none text-dark fw-semibold">
                                <?= htmlspecialchars($news->getTitre()); ?>
                            </a>
                        </h5>
                        <small class="text-muted">
                            <?= date('d/m/Y H:i', strtotime($news->getCreatedAt())); ?>
                        </small>
                    </div>
                    <p class="mb-1 text-muted">
                        Créé par :
                        <?php if ($author): ?>
                            <?= htmlspecialchars($author->getNom()) . ' ' . htmlspecialchars($author->getPrenom()); ?>
                        <?php else: ?>
                            <em>Auteur inconnu</em>
                        <?php endif; ?>
                    </p>

                    <?php if ($authController->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
                        <div class="mt-2">
                            <a href="index.php?page=edit-news&id=<?= $news->getId(); ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil-square"></i> Éditer
                            </a>
                            <a href="index.php?page=delete-news&id=<?= $news->getId(); ?>"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Voulez-vous vraiment supprimer cette actualité ?');">
                                <i class="bi bi-trash"></i> Supprimer
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted text-center mt-4">Aucune actualité disponible pour le moment.</p>
    <?php endif; ?>

    <!-- Pagination Bootstrap -->
    <?php if (isset($totalPages) && $totalPages > 1): ?>
        <nav aria-label="Pagination des actualités" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Page précédente -->
                <li class="page-item <?= ($pageNum <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="index.php?page=news&page_num=<?= max(1, $pageNum - 1) ?>"
                        aria-label="Précédent">
                        &laquo;
                    </a>
                </li>

                <!-- Numéros de page -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $pageNum) ? 'active' : '' ?>">
                        <a class="page-link" href="index.php?page=news&page_num=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Page suivante -->
                <li class="page-item <?= ($pageNum >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link"
                        href="index.php?page=news&page_num=<?= min($totalPages, $pageNum + 1) ?>"
                        aria-label="Suivant">
                        &raquo;
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>