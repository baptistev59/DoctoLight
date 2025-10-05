<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <h2 class="text-primary mb-4 border-bottom pb-2">Modifier l'actualité</h2>

    <form action="index.php?page=update-news&id=<?= $news->getId(); ?>" method="post" enctype="multipart/form-data" class="bg-light p-4 rounded shadow-sm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
            <label for="titre" class="form-label fw-semibold">Titre</label>
            <input type="text" id="titre" name="titre" class="form-control" value="<?= htmlspecialchars($news->getTitre()); ?>" required>
        </div>

        <div class="mb-3">
            <label for="contenu" class="form-label fw-semibold">Contenu</label>
            <textarea id="contenu" name="contenu" rows="8" class="form-control" required><?= htmlspecialchars($news->getContenu()); ?></textarea>
        </div>

        <!-- Aperçu de l’image actuelle -->
        <?php if ($news->getImage()): ?>
            <div class="text-center mb-4">
                <img src="<?= BASE_URL ?>uploads/news/<?= htmlspecialchars($news->getImage()); ?>"
                    alt="Illustration de l'actualité"
                    class="img-fluid rounded shadow-sm"
                    style="max-height: 400px; object-fit: cover;">
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="image" class="form-label fw-semibold">Nouvelle image (optionnelle)</label>
            <input type="file" id="image" name="image" accept="image/*" class="form-control">
            <small class="text-muted">Laisser vide pour conserver l'image actuelle.</small>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Mettre à jour
            </button>
            <a href="index.php?page=news" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </form>
</div>

<script>
    document.querySelector('form').addEventListener("submit", () => tinymce.triggerSave());
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>