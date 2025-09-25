<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Éditer l'utilisateur</h1>

<?php if (!empty($_SESSION['success'])): ?>
    <div style="color: green;"><?= htmlspecialchars($_SESSION['success']);
                                unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div style="color: red;"><?= htmlspecialchars($_SESSION['error']);
                                unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php
// Déterminer les valeurs à afficher dans le formulaire selon GET ou POST
$nom = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['nom'] ?? '') : "";
$prenom = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['prenom'] ?? '') : $user->getPrenom();
$email = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['email'] ?? '') : $user->getEmail();
$dateNaissance = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['date_naissance'] ?? '') : $user->getDateNaissance();
$isActive = $_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['is_active']) : $user->isActive();
$selectedRoles = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['roles'] ?? []) : $user->getRoles();
?>

<form method="POST" action="<?= BASE_URL ?>index.php?page=users_edit&id=<?= $user->getId() ?>">
    <label>Nom :</label><br>
    <input type="text" name="nom" value="<?= htmlspecialchars($nom) ?>" required><br>

    <label>Prénom :</label><br>
    <input type="text" name="prenom" value="<?= htmlspecialchars($prenom) ?>" required><br>

    <label>Email :</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required><br>

    <label>Date de naissance :</label><br>
    <input type="date" name="date_naissance" value="<?= htmlspecialchars($dateNaissance) ?>"><br>

    <label>Actif :</label>
    <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?>><br>

    <label>Rôles :</label><br>
    <?php foreach ($roles as $role): ?>
        <input type="checkbox" name="roles[]" value="<?= htmlspecialchars($role->getName()) ?>"
            <?= in_array($role->getName(), $selectedRoles, true) ? 'checked' : '' ?>>
        <?= htmlspecialchars($role->getName()) ?><br>
    <?php endforeach; ?>

    <button type="submit">Mettre à jour</button>
</form>

<a href="<?= BASE_URL ?>index.php?page=users">Retour à la liste</a>

<?php include __DIR__ . '/../layouts/footer.php'; ?>