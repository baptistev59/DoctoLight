<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <h2 class="text-primary mb-4 border-bottom pb-2">Créer un nouveau service</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form id="serviceForm" action="index.php?page=services_store" method="post"
        enctype="multipart/form-data" class="bg-light p-4 rounded shadow-sm">

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- Nom -->
        <div class="mb-3">
            <label for="nom" class="form-label fw-semibold">Nom du service</label>
            <input type="text" id="nom" name="nom" class="form-control"
                placeholder="Ex : Détartrage, Consultation, Urgence..." required>
        </div>

        <!-- Durée -->
        <div class="mb-3">
            <label for="duree" class="form-label fw-semibold">Durée (en minutes)</label>
            <input type="number" id="duree" name="duree" class="form-control" value="30" min="10" step="5" required>
        </div>

        <!-- Description -->
        <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" rows="6" class="form-control"
                placeholder="Décrivez le service proposé..."></textarea>
        </div>

        <!-- Image -->
        <div class="mb-3">
            <label for="image" class="form-label fw-semibold">Image du service (optionnelle)</label>
            <input type="file" id="image" name="image" accept="image/*" class="form-control">
            <small class="text-muted">Formats acceptés : JPG, PNG, WEBP — taille max : 2 Mo</small>
        </div>

        <!-- Actif / Inactif -->
        <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
            <label class="form-check-label" for="is_active">Service actif</label>
        </div>

        <!-- Boutons -->
        <div class="d-flex justify-content-between align-items-center">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Créer le service
            </button>
            <a href="index.php?page=services" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
        </div>
    </form>
</div>
<script>
    // Sauvegarde du contenu TinyMCE avant envoi
    document.getElementById("serviceForm").addEventListener("submit", function() {
        tinymce.triggerSave();
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>