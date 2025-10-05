<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">

    <h1 class="h3 mb-4 text-primary">
        <i class="bi bi-person-plus"></i> Créer un nouvel utilisateur
    </h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" required
                value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" name="prenom" id="prenom" class="form-control" required
                value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email" name="email" id="email" class="form-control" required
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date de naissance</label>
            <input type="date" name="date_naissance" id="date_naissance" class="form-control"
                value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="roles" class="form-label">Rôles</label>
            <div class="border rounded p-3">
                <?php foreach ($roles as $role): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="roles[]"
                            id="role_<?= htmlspecialchars($role->getName()) ?>"
                            value="<?= htmlspecialchars($role->getName()) ?>">
                        <label class="form-check-label" for="role_<?= htmlspecialchars($role->getName()) ?>">
                            <?= htmlspecialchars($role->getName()) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
            <label class="form-check-label" for="is_active">Activer le compte</label>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Enregistrer
        </button>
        <a href="<?= BASE_URL ?>index.php?page=users" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-arrow-left"></i> Annuler
        </a>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>