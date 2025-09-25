<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Créer un utilisateur</h1>

<?php if (!empty($_SESSION['success'])): ?>
    <div style="color: green;"><?= htmlspecialchars($_SESSION['success']);
                                unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div style="color: red;"><?= htmlspecialchars($_SESSION['error']);
                                unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>index.php?page=users_create">
    <label>Nom :</label><br>
    <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required><br>

    <label>Prénom :</label><br>
    <input type="text" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required><br>

    <label>Email :</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required><br>

    <label>Date de naissance :</label><br>
    <input type="date" name="date_naissance" value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>"><br>

    <label>Actif :</label>
    <input type="checkbox" name="is_active" value="1" <?= ($_POST['is_active'] ?? 1) ? 'checked' : '' ?>><br>

    <label>Rôles :</label><br>
    <?php foreach ($roles as $role): ?>
        <input type="checkbox" name="roles[]" value="<?= $role->getId() ?>"
            <?= isset($_POST['roles']) && in_array($role->getId(), $_POST['roles']) ? 'checked' : '' ?>>
        <?= htmlspecialchars($role->getName()) ?><br>
    <?php endforeach; ?>

    <button type="submit">Créer</button>
</form>

<a href="<?= BASE_URL ?>index.php?page=users">Retour à la liste</a>

<?php include __DIR__ . '/../layouts/footer.php'; ?>