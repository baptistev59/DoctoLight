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
$isSelfEdit = $currentUser && $currentUser->getId() === $user->getId();
?>

<form method="POST" action="">
    <!-- CSRF token obligatoire -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <label>Nom :</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($user->getNom()) ?>" required><br>

    <label>Prénom :</label>
    <input type="text" name="prenom" value="<?= htmlspecialchars($user->getPrenom()) ?>" required><br>

    <label>Email :</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>" required><br>

    <label>Date de naissance :</label>
    <input type="date" name="date_naissance" value="<?= htmlspecialchars($user->getDateNaissance() ?? '') ?>"><br>

    <label>Nouveau mot de passe (laisser vide si inchangé) :</label>
    <input type="password" name="password"><br>

    <?php if ($isAdminOrStaff && !$isSelfEdit): ?>
        <label>Actif :</label>
        <input type="checkbox" name="is_active" <?= $user->isActive() ? 'checked' : '' ?>><br>

        <label>Rôles :</label><br>
        <?php foreach ($roles as $role): ?>
            <label>
                <input type="checkbox" name="roles[]" value="<?= $role->getId() ?>"
                    <?= in_array($role->getName(), array_map(fn($r) => $r->getName(), $user->getRoles())) ? 'checked' : '' ?>>
                <?= htmlspecialchars($role->getName()) ?>
            </label><br>
        <?php endforeach; ?>
    <?php endif; ?>

    <button type="submit">Enregistrer</button>
</form>

<a href="<?= BASE_URL ?>index.php?page=users_view&id=<?= $user->getId() ?>">Annuler</a>

<?php include __DIR__ . '/../layouts/footer.php'; ?>