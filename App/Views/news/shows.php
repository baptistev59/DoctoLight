<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2><?= htmlspecialchars($news->getTitre()); ?></h2>
<?= nl2br($news->getContenu()); ?>
<p><small>Créé par : <?php if ($author): ?>
            <?= htmlspecialchars($author->getNom()) . " " . htmlspecialchars($author->getPrenom()); ?>
        <?php else: ?>
            Auteur inconnu
        <?php endif; ?> le <?= $news->getCreatedAt(); ?></small></p>


<?php

// Vérifie si l’utilisateur connecté est Admin ou Secrétaire
if (isset($currentUser) && isset($currentRoles)) {
    $currentHighestRole = $currentUser->getHighestRole();
    if ($currentHighestRole === "ADMIN" || $currentHighestRole === "SECRETAIRE"): ?>
        <a href="index.php?page=edit-news&id=<?= $news->getId(); ?>">Éditer</a>
        <a href="index.php?page=delete-news&id=<?= $news->getId(); ?>" onclick="return confirm('Voulez-vous vraiment supprimer cette news ?');">Supprimer</a>
<?php endif;
}
?>
<a href="index.php?page=news">Retour à la liste</a>

<?php include __DIR__ . '/../layouts/footer.php'; ?>