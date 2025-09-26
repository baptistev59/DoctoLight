<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-5">
    <h2>Inscription</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="index.php?page=register">
        <!-- Token CSRF -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" name="nom" id="nom" class="form-control" required
                value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" name="prenom" id="prenom" class="form-control" required
                value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email" name="email" id="email" class="form-control" required
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date de naissance</label>
            <input type="date" name="date_naissance" id="date_naissance" class="form-control"
                value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">S'inscrire</button>
    </form>

    <p class="mt-3">Déjà un compte ? <a href="index.php?page=login">Connexion</a></p>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>