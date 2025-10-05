<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">

    <!-- Titre de l’actualité -->
    <h2 class="text-primary mb-4 border-bottom pb-2">
        <?= htmlspecialchars($news->getTitre()); ?>
    </h2>

    <!-- Image principale -->
    <?php if ($news->getImage()): ?>
        <div class="text-center mb-4">
            <img src="<?= BASE_URL ?>uploads/news/<?= htmlspecialchars($news->getImage()) ?>"
                alt="Illustration de l'actualité"
                class="img-fluid rounded shadow-sm"
                style="max-height: 400px; object-fit: cover;">
        </div>
    <?php endif; ?>

    <!-- Contenu de la news -->
    <div class="mb-4">
        <p class="fs-5 text-justify">
            <?= nl2br($news->getContenu()); ?>
        </p>
    </div>

    <!-- Auteur et date -->
    <p class="text-muted small mb-5">
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
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <?php
        if (isset($currentUser) && isset($currentRoles)) {
            $currentHighestRole = $currentUser->getHighestRole();
            if (in_array($currentHighestRole, ["ADMIN", "SECRETAIRE"])): ?>
                <a href="index.php?page=edit-news&id=<?= $news->getId(); ?>"
                    class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i> Éditer
                </a>
                <a href="index.php?page=delete-news&id=<?= $news->getId(); ?>"
                    class="btn btn-danger btn-sm"
                    onclick="return confirm('Voulez-vous vraiment supprimer cette actualité ?');">
                    <i class="bi bi-trash"></i> Supprimer
                </a>
        <?php endif;
        } ?>

        <a href="index.php?page=news" class="btn btn-outline-secondary btn-sm ms-auto">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <!-- Navigation entre actualités -->
    <div class="d-flex justify-content-between mt-4">
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