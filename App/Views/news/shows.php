<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="news-container">

    <!-- Titre de l’actualité -->
    <h2><?= htmlspecialchars($news->getTitre()); ?></h2>

    <!-- Image principale -->
    <?php if ($news->getImage()): ?>
        <div class="news-image">
            <img src="<?= BASE_URL ?>uploads/news/<?= htmlspecialchars($news->getImage()) ?>"
                alt="Illustration de l'actualité"
                class="img-fluid">
        </div>
    <?php endif; ?>

    <!-- Contenu de la news -->
    <div class="news-content">
        <?= nl2br($news->getContenu()); ?>
    </div>

    <!-- Auteur et date -->
    <div class="news-meta">
        <i class="bi bi-person-circle"></i>
        Créé par :
        <?php if ($author): ?>
            <strong><?= htmlspecialchars($author->getPrenom()) . " " . htmlspecialchars($author->getNom()); ?></strong>
        <?php else: ?>
            <em>Auteur inconnu</em>
        <?php endif; ?>
        — <i class="bi bi-calendar3"></i>
        publié le <?= htmlspecialchars($news->getCreatedAt('d/m/Y à H:i')); ?>
    </div>

    <!-- Actions -->
    <div class="news-actions">
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

        <a href="index.php?page=news" class="btn btn-outline-secondary btn-sm ms-auto">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <!-- Navigation entre actualités -->
    <div class="news-navigation">
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
    </div>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>