<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>Liste des Actualités</h2>

<?php if (!empty($newsList)): ?>
    <ul>
        <?php foreach ($newsList as $news): ?>
            <li>
                <a href="index.php?page=news_show&id=<?= $news->getId(); ?>">
                    <?= htmlspecialchars($news->getTitre()); ?>
                </a>
                <small>Créé par : <?= $news->getCreatedBy(); ?> le <?= $news->getCreatedAt(); ?></small>
                <br>
                <a href="index.php?page=edit-news&id=<?= $news->getId(); ?>">Éditer</a> |
                <a href="index.php?page=delete-news&id=<?= $news->getId(); ?>" onclick="return confirm('Voulez-vous vraiment supprimer cette news ?');">Supprimer</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucune actualité disponible pour le moment.</p>
<?php endif; ?>
<span>
    <a href="index.php?page=create-news">Créer une actualité</a>
</span>

<?php include __DIR__ . '/../layouts/footer.php'; ?>