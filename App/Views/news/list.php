<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>Liste des Actualités</h2>

<?php if (!empty($newsWithAuthors)): ?>
    <ul>
        <?php foreach ($newsWithAuthors as $item): ?>
            <?php $news = $item['news']; ?>
            <?php $author = $item['author']; ?>
            <li>
                <a href="index.php?page=news_show&id=<?= $news->getId(); ?>">
                    <?= htmlspecialchars($news->getTitre()); ?>
                </a>
                <small>
                    Créé par :
                    <?php if ($author): ?>
                        <?= htmlspecialchars($author->getNom()) . " " . htmlspecialchars($author->getPrenom()); ?>
                    <?php else: ?>
                        Auteur inconnu
                    <?php endif; ?>
                    le <?= htmlspecialchars($news->getCreatedAt()); ?>
                </small>
                <br>

                <?php if ($authController->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
                    <a href="index.php?page=edit-news&id=<?= $news->getId(); ?>">Éditer</a> |
                    <a href="index.php?page=delete-news&id=<?= $news->getId(); ?>"
                        onclick="return confirm('Voulez-vous vraiment supprimer cette news ?');">
                        Supprimer
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucune actualité disponible pour le moment.</p>
<?php endif; ?>

<?php if ($authController->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
    <span>
        <a href="index.php?page=create-news">Créer une actualité</a>
    </span>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>