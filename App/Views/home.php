<?php include __DIR__ . '/../Views/layouts/header.php'; ?>

<h2>Bienvenue sur DoctoLight</h2>
<p>Ceci est la page d'accueil.</p>

<h3>Dernières actualités</h3>
<?php if (!empty($news)): ?>
    <ul>
        <?php foreach ($news as $article): ?>
            <li>
                <a href="index.php?page=news_show&id=<?= $article->getId(); ?>">
                    <?= htmlspecialchars($article->getTitre()); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucune actualité pour le moment.</p>
<?php endif; ?>
<span><a href="index.php?page=news">Voir toutes les news</a></span>

<?php include __DIR__ . '/../Views/layouts/footer.php'; ?>