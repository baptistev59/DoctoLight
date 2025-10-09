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
        <div class="row g-4">
            <?php foreach ($newsWithAuthors as $item): ?>
                <?php $news = $item['news']; ?>
                <?php $author = $item['author']; ?>

                <div class="col-md-6 col-lg-4">
                    <div class="card news-card h-100 border-0 shadow-sm">
                        <?php if ($news->getImage()): ?>
                            <img src="<?= BASE_URL ?>uploads/news/<?= htmlspecialchars($news->getImage()); ?>"
                                class="card-img-top news-thumb"
                                alt="Illustration de <?= htmlspecialchars($news->getTitre()); ?>">
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-2 text-primary">
                                <a href="index.php?page=news_show&id=<?= $news->getId(); ?>"
                                    class="stretched-link text-decoration-none text-dark">
                                    <?= htmlspecialchars($news->getTitre()); ?>
                                </a>
                            </h5>

                            <small class="text-muted mb-2">
                                <i class="bi bi-calendar3"></i>
                                <?= date('d/m/Y', strtotime($news->getCreatedAt())); ?>
                                <?php if ($author): ?>
                                    — <i class="bi bi-person"></i>
                                    <?= htmlspecialchars($author->getPrenom()) . ' ' . htmlspecialchars($author->getNom()); ?>
                                <?php endif; ?>
                            </small>

                            <p class="card-text flex-grow-1 text-muted small">
                                <?= htmlspecialchars(mb_strimwidth(strip_tags($news->getContenu()), 0, 120, '...')); ?>
                            </p>

                            <?php if ($authController->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
                                <div class="mt-auto d-flex justify-content-between pt-2 border-top">
                                    <a href="index.php?page=edit-news&id=<?= $news->getId(); ?>"
                                        class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
                                    <a href="index.php?page=delete-news&id=<?= $news->getId(); ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Voulez-vous vraiment supprimer cette actualité ?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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