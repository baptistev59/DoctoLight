<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Édition de l'utilisateur</h1>

<?php if (!empty($_SESSION['success'])): ?>
    <div style="color: green;"><?= htmlspecialchars($_SESSION['success']);
                                unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div style="color: red;"><?= htmlspecialchars($_SESSION['error']);
                                unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php
$currentUser = $_SESSION['user'] ?? null;
$isAdminOrStaff = $currentUser && $currentUser->hasRole(['ADMIN', 'MEDECIN', 'SECRETAIRE']);
$isSelfEdit = $currentUser && $currentUser->getId() === $userToEdit->getId();
?>

<form method="POST" action="">
    <!-- CSRF token obligatoire -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <label>Nom :</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($userToEdit->getNom()) ?>" required><br>

    <label>Prénom :</label>
    <input type="text" name="prenom" value="<?= htmlspecialchars($userToEdit->getPrenom()) ?>" required><br>

    <label>Email :</label>
    <input type="email" name="email" value="<?= htmlspecialchars($userToEdit->getEmail()) ?>" required><br>

    <label>Date de naissance :</label>
    <input type="date" name="date_naissance" value="<?= htmlspecialchars($userToEdit->getDateNaissance() ?? '') ?>"><br>

    <label>Nouveau mot de passe (laisser vide si inchangé) :</label>
    <input type="password" name="password"><br>

    <label>Confirmer le mot de passe :</label>
    <input type="password" name="password_confirm"><br>

    <?php if ($isAdminOrStaff && !$isSelfEdit): ?>
        <label>Actif :</label>
        <input type="checkbox" name="is_active" <?= $userToEdit->isActive() ? 'checked' : '' ?>><br>

        <label>Rôles :</label><br>
        <?php foreach ($roles as $role): ?>
            <label>
                <input type="checkbox" name="roles[]" value="<?= $role->getName() ?>"
                    <?= in_array($role->getName(), array_map(fn($r) => $r->getName(), $userToEdit->getRoles())) ? 'checked' : '' ?>>
                <?= htmlspecialchars($role->getName()) ?>
            </label><br>
        <?php endforeach; ?>
    <?php endif; ?>

    <button type="submit">Enregistrer</button>
</form>

<a href="<?= BASE_URL ?>index.php?page=users_view&id=<?= $userToEdit->getId() ?>">Annuler</a>

<?php include __DIR__ . '/../layouts/footer.php'; ?>