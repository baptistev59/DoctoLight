<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>Modifier la news</h2>

<form action="index.php?page=update-news&id=<?= $news->getId(); ?>" method="post">
    <div>
        <!-- Insertion du token de sécurité -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <label for="titre">Titre :</label>
        <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($news->getTitre()); ?>" required>
    </div>
    <div>
        <label for="contenu">Contenu :</label>
        <textarea id="contenu" name="contenu" rows="5" required><?= htmlspecialchars($news->getContenu()); ?></textarea>
    </div>
    <button type="submit">Mettre à jour</button>
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>