<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5 news-show">
    <div class="card card-base card-news shadow-sm">
        <div class="row g-0">

            <!-- Image principale -->
            <?php if ($news->getImage()): ?>
                <div class="col-md-5">
                    <img src="<?= BASE_URL ?>uploads/news/<?= htmlspecialchars($news->getImage()) ?>"
                        alt="<?= htmlspecialchars($news->getTitre()) ?>"
                        class="img-fluid w-100 object-fit-cover rounded-start">
                </div>
            <?php endif; ?>

            <!-- Titre de l’actualité -->
            <div class="col-md-7">
                <div class="card-body">
                    <h2 class="card-title text-primary mb-3">
                        <?= htmlspecialchars($news->getTitre()) ?>
                    </h2>

                    <!-- Contenu de la news -->
                    <P class="card-text">
                        <?= nl2br($news->getContenu()); ?>
                    </P>

                    <!-- Auteur et date -->
                    <p class="text-muted mt-3 small">
                        <i class="bi bi-person-circle"></i>
                        Créé par :
                        <?php if ($author): ?>
                            <strong><?= htmlspecialchars($author->getPrenom()) . " " . htmlspecialchars($author->getNom()); ?></strong>
                        <?php else: ?>
                            <em>Auteur inconnu</em>
                        <?php endif; ?>
                        — <i class="bi bi-calendar3"></i>
                        publié le <?= htmlspecialchars($news->getCreatedAt('d/m/Y à H:i')); ?>
                    </p>

                    <!-- Actions -->
                    <div class="text-center mt-4">
                        <?php if (isset($currentUser) && isset($currentRoles)): ?>
                            <?php $currentHighestRole = $currentUser->getHighestRole(); ?>
                            <?php if (in_array($currentHighestRole, ["ADMIN", "SECRETAIRE"])): ?>
                                <a href="index.php?page=edit-news&id=<?= $news->getId(); ?>"
                                    class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i> Éditer
                                </a>
                                <a href="index.php?page=delete-news&id=<?= $news->getId(); ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Voulez-vous vraiment supprimer cette actualité ?');">
                                    <i class="bi bi-trash"></i> Supprimer
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>


                    </div>

                    <!-- Navigation entre actualités -->
                    <div class="news-navigation mt-4 d-flex flex-wrap justify-content-between align-items-center">
                        <?php if (!empty($previousId)): ?>
                            <a href="index.php?page=news_show&id=<?= $previousId; ?>"
                                class="btn btn-outline-primary">
                                <i class="bi bi-chevron-left"></i> Actualité précédente
                            </a>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>

                        <?php if (!empty($nextId)): ?>
                            <a href="index.php?page=news_show&id=<?= $nextId; ?>"
                                class="btn btn-outline-primary">
                                Actualité suivante <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                        <a href="index.php?page=news" class="btn btn-outline-secondary btn-sm ms-auto">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>