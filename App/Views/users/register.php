<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh;">
    <div class="card form-card">
        <div class="text-center mb-4">
            <i class="bi bi-person-plus text-success" style="font-size: 3rem;"></i>
            <h2 class="mt-2 text-success">Inscription</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?page=register">
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control" required
                        value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" name="prenom" id="prenom" class="form-control" required
                        value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Adresse email</label>
                <input type="email" name="email" id="email" class="form-control" required
                    placeholder="exemple@domaine.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="date_naissance" class="form-label">Date de naissance</label>
                <input type="date" name="date_naissance" id="date_naissance" class="form-control"
                    value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" name="password" id="password" class="form-control"
                    placeholder="••••••••" required>
            </div>

            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control"
                    placeholder="••••••••" required>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-person-check"></i> S'inscrire
                </button>
            </div>
        </form>

        <div class="text-center mt-3">
            <p class="mb-0">
                Déjà un compte ?
                <a href="index.php?page=login" class="text-decoration-none text-primary fw-semibold">
                    Se connecter
                </a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>