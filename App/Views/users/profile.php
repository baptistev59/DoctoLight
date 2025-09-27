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
<p><strong>Date de naissance :</strong> <?= htmlspecialchars($userToView->getDateNaissance() ?? '-') ?></p>

<?php
$currentUser = $_SESSION['user'] ?? null;
$isAdminOrStaff = $currentUser && $currentUser->hasRole(['ADMIN', 'MEDECIN', 'SECRETAIRE']);
?>

<?php if ($isAdminOrStaff): ?>
    <p><strong>Actif :</strong> <?= $userToView->isActive() ? 'Oui' : 'Non' ?></p>

    <p><strong>Rôles :</strong>
        <?= implode(', ', array_map(fn($r) => htmlspecialchars($r->getName()), $userToView->getRoles())) ?>
    </p>
<?php endif; ?>

<hr>

<?php if ($userToView->hasRole('PATIENT')): ?>
    <h3>Informations Patient</h3>
    <p>Dossiers médicaux, historique des rendez-vous, prescriptions.</p>
<?php endif; ?>

<?php if ($userToView->hasRole('MEDECIN')): ?>
    <h3>Informations Médecin</h3>
    <p>Planning, rendez-vous à venir, etc.</p>
<?php endif; ?>

<?php if ($userToView->hasRole('SECRETAIRE')): ?>
    <h3>Informations Secrétaire</h3>
    <p>Tableau de bord.</p>
<?php endif; ?>

<?php if ($userToView->hasRole('ADMIN')): ?>
    <h3>Informations Administrateur</h3>
    <p>Accès complet au système, gestion des utilisateurs et des rôles.</p>
<?php endif; ?>

<hr>

<?php if ($currentUser && $currentUser->getId() === $userToView->getId()): ?>
    <!-- Le user édite son propre profil -->
    <a href="<?= BASE_URL ?>index.php?page=users_edit&id=<?= $userToView->getId() ?>">Modifier mon profil</a>
<?php elseif ($isAdminOrStaff): ?>
    <!-- Admin ou staff édite un autre utilisateur -->
    <a href="<?= BASE_URL ?>index.php?page=users_edit&id=<?= $userToView->getId() ?>">Éditer</a>
<?php endif; ?>

<?php if ($isAdminOrStaff): ?>
    | <a href="<?= BASE_URL ?>index.php?page=users">Retour à la liste</a>
<?php endif; ?>

<?php if ($currentUser && $currentUser->hasRole('ADMIN') && $userToView->getId() !== $currentUser->getId()): ?>
    <form method="POST" action="<?= BASE_URL ?>index.php?page=users_toggle&id=<?= $userToView->getId() ?>" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="id" value="<?= $userToView->getId() ?>">
        <button type="submit" onclick="return confirm('Voulez-vous vraiment <?= $userToView->isActive() ? 'désactiver' : 'activer' ?> cet utilisateur ?');">
            <?= $userToView->isActive() ? 'Désactiver' : 'Activer' ?>
        </button>
    </form>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>