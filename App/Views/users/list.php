<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Liste des utilisateurs</h1>

<?php if (!empty($_SESSION['success'])): ?>
    <div style="color: green;"><?= htmlspecialchars($_SESSION['success']);
                                unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div style="color: red;"><?= htmlspecialchars($_SESSION['error']);
                                unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Formulaire de recherche -->
<form method="GET" action="<?= BASE_URL ?>index.php">
    <input type="hidden" name="page" value="users">
    <input type="text" name="search" placeholder="Rechercher..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <button type="submit">Rechercher</button>
</form>

<?php
$order = $_GET['order'] ?? 'ASC';
$newOrder = $order === 'ASC' ? 'DESC' : 'ASC';
$searchParam = urlencode($_GET['search'] ?? '');
$sortParam = urlencode($_GET['sort'] ?? 'id');

// Vérifier si l'utilisateur connecté est ADMIN
$roles = array_map(fn($role) => strtoupper($role->getName()), $_SESSION['user']->getRoles());
$isAdmin = $_SESSION['user']->hasRole('ADMIN');

?>

<!-- Lien création utilisateur (seulement si ADMIN) -->
<?php if ($isAdmin): ?>
    <a href="<?= BASE_URL ?>index.php?page=users_create">Créer un nouvel utilisateur</a>
<?php endif; ?>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th><a href="<?= BASE_URL ?>index.php?page=users&sort=nom&order=<?= $newOrder ?>&search=<?= $searchParam ?>">Nom</a></th>
            <th><a href="<?= BASE_URL ?>index.php?page=users&sort=prenom&order=<?= $newOrder ?>&search=<?= $searchParam ?>">Prénom</a></th>
            <th><a href="<?= BASE_URL ?>index.php?page=users&sort=email&order=<?= $newOrder ?>&search=<?= $searchParam ?>">Email</a></th>
            <th>Actif</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><a href="<?= BASE_URL ?>index.php?page=users_view&id=<?= $user->getId() ?>"><?= htmlspecialchars($user->getNom()) ?></a></td>
                    <td><?= htmlspecialchars($user->getPrenom()) ?></td>
                    <td><?= htmlspecialchars($user->getEmail()) ?></td>
                    <td>
                        <?php if ($isAdmin && $user->getId() !== $_SESSION['user']->getId()): ?>
                            <form method="POST" action="<?= BASE_URL ?>index.php?page=users_toggle&id=<?= $user->getId() ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="id" value="<?= $user->getId() ?>">
                                <input type="checkbox" name="is_active" <?= $user->isActive() ? 'checked' : '' ?>
                                    onclick="if(!confirm('Voulez-vous vraiment <?= $user->isActive() ? 'désactiver' : 'activer' ?> cet utilisateur ?')) return false; this.form.submit();">
                            </form>
                        <?php else: ?>
                            <?= $user->isActive() ? 'Oui' : 'Non' ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($isAdmin): ?>
                            <a href="<?= BASE_URL ?>index.php?page=users_edit&id=<?= $user->getId() ?>">Éditer</a> |
                            <a href="<?= BASE_URL ?>index.php?page=users_delete&id=<?= $user->getId() ?>"
                                onclick="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');">Supprimer</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Aucun utilisateur trouvé.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Pagination -->
<?php if (isset($totalPages) && $totalPages > 1): ?>
    <div>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= BASE_URL ?>index.php?page=users&search=<?= $searchParam ?>&sort=<?= $sortParam ?>&order=<?= $order ?>&page_num=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>