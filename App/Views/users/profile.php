<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Fiche de l'utilisateur</h1>

<?php if (!empty($_SESSION['success'])): ?>
    <div style="color: green;"><?= htmlspecialchars($_SESSION['success']);
                                unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div style="color: red;"><?= htmlspecialchars($_SESSION['error']);
                                unset($_SESSION['error']); ?></div>
<?php endif; ?>

<p><strong>Nom :</strong> <?= htmlspecialchars($userToView->getNom()) ?></p>
<p><strong>Prénom :</strong> <?= htmlspecialchars($userToView->getPrenom()) ?></p>
<p><strong>Email :</strong> <?= htmlspecialchars($userToView->getEmail()) ?></p>
<p><strong>Date de naissance :</strong> <?= htmlspecialchars($userToView->getDateNaissance()) ?></p>
<p><strong>Actif :</strong>
    <?= $userToView->isActive() ? 'Oui' : 'Non' ?>
</p>

<?php if ($_SESSION['user']->hasRole('ADMIN') && $userToView->getId() !== $_SESSION['user']->getId()): ?>
    <form method="POST" action="<?= BASE_URL ?>index.php?page=users_toggle" style="display:inline;">
        <input type="hidden" name="id" value="<?= $userToView->getId() ?>">
        <button type="submit" onclick="return confirm('Voulez-vous vraiment <?= $userToView->isActive() ? 'désactiver' : 'activer' ?> cet utilisateur ?');">
            <?= $userToView->isActive() ? 'Désactiver' : 'Activer' ?>
        </button>
    </form>
<?php endif; ?>

<p><strong>Rôles :</strong> <?= implode(', ', array_map(fn($r) => htmlspecialchars($r->getName()), $userToView->getRoles())) ?></p>

<a href="<?= BASE_URL ?>index.php?page=users_edit&id=<?= $userToView->getId() ?>">Éditer</a> |
<a href="<?= BASE_URL ?>index.php?page=users">Retour à la liste</a>

<?php include __DIR__ . '/../layouts/footer.php'; ?>