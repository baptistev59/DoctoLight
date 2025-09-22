<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2><?= htmlspecialchars($news->getTitre()); ?></h2>
<p><?= nl2br(htmlspecialchars($news->getContenu())); ?></p>
<p><small>Créé par : <?= $news->getCreatedBy(); ?> le <?= $news->getCreatedAt(); ?></small></p>


<a href="index.php?page=edit-news&id=<?= $news->getId(); ?>">Éditer</a>
<a href="index.php?page=delete-news&id=<?= $news->getId(); ?>" onclick="return confirm('Voulez-vous vraiment supprimer cette news ?');">Supprimer</a>
<a href="index.php?page=news">Retour à la liste</a>

<?php include __DIR__ . '/../layouts/footer.php'; ?>