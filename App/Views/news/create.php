<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <h2 class="text-primary mb-4 border-bottom pb-2">Créer une nouvelle actualité</h2>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'validation'): ?>
        <div class="alert alert-danger">
            Le titre et le contenu doivent contenir au moins 3 caractères.
        </div>
    <?php endif; ?>

    <form id="newsForm" action="index.php?page=create-news-valid" method="post" enctype="multipart/form-data" class="bg-light p-4 rounded shadow-sm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
            <label for="titre" class="form-label fw-semibold">Titre</label>
            <input type="text" id="titre" name="titre" class="form-control" placeholder="Titre de l'actualité" required>
        </div>

        <div class="mb-3">
            <label for="contenu" class="form-label fw-semibold">Contenu</label>
            <textarea id="contenu" name="contenu" rows="8" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label fw-semibold">Image (optionnelle)</label>
            <input type="file" id="image" name="image" accept="image/*" class="form-control">
            <small class="text-muted">Formats acceptés : JPG, PNG, WEBP — taille max : 2 Mo</small>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Créer la news
            </button>
            <a href="index.php?page=news" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </form>
</div>

<script>
    // Sauvegarde du contenu TinyMCE avant envoi
    document.getElementById("newsForm").addEventListener("submit", function() {
        tinymce.triggerSave();
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>