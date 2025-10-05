<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4 text-primary">
        <i class="bi bi-person-gear"></i> Édition de l'utilisateur
    </h1>

    <!-- Messages flash -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php
    $currentUser = $_SESSION['user'] ?? null;
    $isAdminOrStaff = $currentUser && $currentUser->hasRole(['ADMIN', 'MEDECIN', 'SECRETAIRE']);
    $isSelfEdit = $currentUser && $currentUser->getId() === $userToEdit->getId();
    ?>

    <form method="POST" action="" class="card p-4 shadow-sm">
        <!-- CSRF token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom :</label>
                <input type="text" class="form-control" name="nom" id="nom"
                    value="<?= htmlspecialchars($userToEdit->getNom()) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="prenom" class="form-label">Prénom :</label>
                <input type="text" class="form-control" name="prenom" id="prenom"
                    value="<?= htmlspecialchars($userToEdit->getPrenom()) ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email :</label>
            <input type="email" class="form-control" name="email" id="email"
                value="<?= htmlspecialchars($userToEdit->getEmail()) ?>" required>
        </div>

        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date de naissance :</label>
            <input type="date" class="form-control" name="date_naissance" id="date_naissance"
                value="<?= htmlspecialchars($userToEdit->getDateNaissance() ?? '') ?>">
        </div>

        <hr class="my-4">

        <h5 class="text-secondary mb-3"><i class="bi bi-key"></i> Mot de passe</h5>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="password" class="form-label">Nouveau mot de passe (laisser vide si inchangé) :</label>
                <input type="password" class="form-control" name="password" id="password">
            </div>
            <div class="col-md-6">
                <label for="password_confirm" class="form-label">Confirmer le mot de passe :</label>
                <input type="password" class="form-control" name="password_confirm" id="password_confirm">
            </div>
        </div>

        <?php if ($isAdminOrStaff && !$isSelfEdit): ?>
            <hr class="my-4">

            <h5 class="text-secondary mb-3"><i class="bi bi-person-badge"></i> Gestion administrative</h5>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                    <?= $userToEdit->isActive() ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Compte actif</label>
            </div>

            <div class="mb-3">
                <label class="form-label">Rôles :</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($roles as $role): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" id="role_<?= $role->getName() ?>"
                                value="<?= $role->getName() ?>"
                                <?= in_array($role->getName(), array_map(fn($r) => $r->getName(), $userToEdit->getRoles())) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role_<?= $role->getName() ?>">
                                <?= htmlspecialchars($role->getName()) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-4 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Enregistrer
            </button>

            <a href="<?= BASE_URL ?>index.php?page=users_view&id=<?= $userToEdit->getId() ?>"
                class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Annuler
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>