<!-- App/Views/users/edit.php -->
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <h2>Modifier l'utilisateur</h2>

    <form method="POST" action="index.php?page=updateUser&id=<?= $user->getId() ?>">
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom"
                value="<?= htmlspecialchars($user->getPrenom()) ?>" required>
        </div>

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom"
                value="<?= htmlspecialchars($user->getNom()) ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email" class="form-control" id="email" name="email"
                value="<?= htmlspecialchars($user->getEmail()) ?>" required>
        </div>

        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date de naissance</label>
            <input type="date" class="form-control" id="date_naissance" name="date_naissance"
                value="<?= htmlspecialchars($user->getDateNaissance() ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="roles" class="form-label">Rôle(s)</label>
            <select id="roles" name="roles[]" class="form-select" multiple required>
                <?php foreach ($config['role_hierarchy'] as $role): ?>
                    <option value="<?= $role ?>"
                        <?= in_array($role, $user->getRoles(), true) ? 'selected' : '' ?>>
                        <?= $role ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Maintenir Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs rôles.</small>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                <?= $user->isActive() ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">Compte actif</label>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="index.php?page=users" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>