<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <h2 class="text-primary mb-4 border-bottom pb-2">Modifier le service</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form id="serviceEditForm"
        action="index.php?page=services_update&id=<?= $service->getId(); ?>"
        method="post"
        enctype="multipart/form-data"
        class="bg-light p-4 rounded shadow-sm">

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- Nom -->
        <div class="mb-3">
            <label for="nom" class="form-label fw-semibold">Nom du service</label>
            <input type="text" id="nom" name="nom" class="form-control"
                value="<?= htmlspecialchars($service->getNom()); ?>" required>
        </div>

        <!-- Durée -->
        <div class="mb-3">
            <label for="duree" class="form-label fw-semibold">Durée (en minutes)</label>
            <input type="number" id="duree" name="duree" class="form-control"
                value="<?= htmlspecialchars($service->getDuree()); ?>" min="10" step="5" required>
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" rows="6" class="form-control rich-text"><?= $service->getDescription(); ?></textarea>
        </div>

        <!-- Image -->
        <div class="mb-3">
            <label for="image" class="form-label fw-semibold">Image actuelle</label>
            <?php if ($service->getImage()): ?>
                <div class="mb-3">
                    <img src="<?= BASE_URL ?>uploads/services/<?= htmlspecialchars($service->getImage()); ?>"
                        alt="Image du service"
                        class="img-fluid service-image">
                </div>
            <?php else: ?>
                <p class="text-muted">Aucune image disponible pour ce service.</p>
            <?php endif; ?>

            <label for="new_image" class="form-label fw-semibold">Remplacer l'image (optionnel)</label>
            <input type="file" id="new_image" name="new_image" accept="image/*" class="form-control">
            <small class="text-muted">Formats acceptés : JPG, PNG, WEBP — taille max : 2 Mo</small>
        </div>

        <!-- Statut actif -->
        <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                <?= $service->isActive() ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_active">Service actif</label>
        </div>

        <!-- Boutons -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Enregistrer les modifications
            </button>
            <a href="index.php?page=services" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </form>
</div>

<script>
    // Initialisation de TinyMCE
    tinymce.init({
        selector: '#description',
        plugins: 'lists link table media',
        toolbar: 'undo redo | bold italic underline | bullist numlist | alignleft aligncenter alignright | link',
        menubar: false,
        height: 250,
        branding: false,
        promotion: false,
        setup: function(editor) {
            editor.on('change', function() {
                tinymce.triggerSave();
            });
        }
    });

    // Sauvegarde du contenu TinyMCE avant envoi
    document.getElementById("serviceEditForm").addEventListener("submit", function() {
        tinymce.triggerSave();
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>