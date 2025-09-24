<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Gestion des utilisateurs</h1>

<?php if ($currentUser && $currentUser->hasRole('ADMIN')): ?>
    <p>
        <a href="index.php?page=users&action=create" class="btn btn-primary">Créer un utilisateur</a>
    </p>
<?php endif; ?>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Rôles</th>
            <th>Date de naissance</th>
            <?php if ($currentUser && $currentUser->hasRole('ADMIN')): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user->getId()) ?></td>
                    <td><?= htmlspecialchars($user->getNom()) ?></td>
                    <td><?= htmlspecialchars($user->getPrenom()) ?></td>
                    <td><?= htmlspecialchars($user->getEmail()) ?></td>
                    <td><?= htmlspecialchars(implode(', ', $user->getRoles())) ?></td>
                    <td><?= htmlspecialchars($user->getDateNaissance()) ?></td>

                    <?php if ($currentUser && $currentUser->hasRole('ADMIN')): ?>
                        <td>
                            <a href="index.php?page=users&action=edit&id=<?= $user->getId() ?>">✏️ Éditer</a> |
                            <a href="index.php?page=users&action=delete&id=<?= $user->getId() ?>"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                🗑️ Supprimer
                            </a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">Aucun utilisateur trouvé.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../layouts/footer.php'; ?>