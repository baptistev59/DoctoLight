<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <h2>Mon profil</h2>

    <?php if (isset($_SESSION['user']) && $_SESSION['user'] instanceof User): ?>
        <p><strong>Nom :</strong> <?= htmlspecialchars($_SESSION['user']->getNom()) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($_SESSION['user']->getPrenom()) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($_SESSION['user']->getEmail()) ?></p>
        <p><strong>Rôle principal :</strong> <?= htmlspecialchars($_SESSION['user']->getHighestRole()) ?></p>
        <a href="index.php?page=logout" class="btn btn-danger">Se déconnecter</a>
    <?php else: ?>
        <p>Vous n’êtes pas connecté.</p>
        <a href="index.php?page=login" class="btn btn-primary">Connexion</a>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>