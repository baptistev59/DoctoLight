<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4 text-primary">
        <i class="bi bi-person-gear"></i> Édition de l'utilisateur
    </h1>

    <!-- Messages flash -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php
    $currentUser = $_SESSION['user'] ?? null;
    $isAdminOrStaff = $currentUser && $currentUser->hasRole(['ADMIN', 'MEDECIN', 'SECRETAIRE']);
    $isSelfEdit = $currentUser && $currentUser->getId() === $userToEdit->getId();
    ?>

    <form id="editUserForm" method="POST" action="" class="card p-4 shadow-sm">
        <!-- CSRF token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="nom" class="form-label">Nom :</label>
                <input type="text" class="form-control" name="nom" id="nom"
                    value="<?= htmlspecialchars($userToEdit->getNom()) ?>" required>
            </div>

            <div class="col-md-6">
                <label for="prenom" class="form-label">Prénom :</label>
                <input type="text" class="form-control" name="prenom" id="prenom"
                    value="<?= htmlspecialchars($userToEdit->getPrenom()) ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email :</label>
            <input type="email" class="form-control" name="email" id="email"
                value="<?= htmlspecialchars($userToEdit->getEmail()) ?>" required>
        </div>

        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date de naissance :</label>
            <input type="date" class="form-control" name="date_naissance" id="date_naissance"
                value="<?= htmlspecialchars($userToEdit->getDateNaissance() ?? '') ?>">
        </div>

        <hr class="my-4">

        <h5 class="text-secondary mb-3"><i class="bi bi-key"></i> Mot de passe</h5>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="password" class="form-label">Nouveau mot de passe (laisser vide si inchangé) :</label>
                <input type="password" class="form-control" name="password" id="password">
                <div id="passwordHelp" class="form-text text-muted">
                    Au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.
                </div>
                <div class="progress mt-2" style="height: 8px;">
                    <div id="passwordStrengthBar" class="progress-bar bg-danger" style="width: 0%;"></div>
                </div>
                <small id="passwordStrengthText" class="form-text text-muted"></small>
            </div>
            <div class="col-md-6">
                <label for="password_confirm" class="form-label">Confirmer le mot de passe :</label>
                <input type="password" class="form-control" name="password_confirm" id="password_confirm">
                <div id="matchMessage" class="form-text"></div>
            </div>
        </div>

        <?php if ($isAdminOrStaff && !$isSelfEdit): ?>
            <hr class="my-4">

            <h5 class="text-secondary mb-3"><i class="bi bi-person-badge"></i> Gestion administrative</h5>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                    <?= $userToEdit->isActive() ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Compte actif</label>
            </div>

            <div class="mb-3">
                <label class="form-label">Rôles :</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($roles as $role): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" id="role_<?= $role->getName() ?>"
                                value="<?= $role->getName() ?>"
                                <?= in_array($role->getName(), array_map(fn($r) => $r->getName(), $userToEdit->getRoles())) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role_<?= $role->getName() ?>">
                                <?= htmlspecialchars($role->getName()) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-4 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Enregistrer
            </button>

            <a href="<?= BASE_URL ?>index.php?page=users_view&id=<?= $userToEdit->getId() ?>"
                class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Annuler
            </a>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('editUserForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirm');
        const matchMessage = document.getElementById('matchMessage');
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('passwordStrengthText');

        function evaluateStrength(pass) {
            let score = 0;
            if (pass.length >= 8) score++;
            if (/[A-Z]/.test(pass)) score++;
            if (/[a-z]/.test(pass)) score++;
            if (/\d/.test(pass)) score++;
            if (/[@$!%*?&]/.test(pass)) score++;
            return score;
        }

        function updateStrengthMeter() {
            const pass = password.value;
            const score = evaluateStrength(pass);
            let width = (score / 5) * 100;
            let color = 'bg-danger';
            let text = 'Très faible';

            if (score >= 4) {
                color = 'bg-success';
                text = 'Fort';
            } else if (score === 3) {
                color = 'bg-warning';
                text = 'Moyen';
            } else if (score === 2) {
                color = 'bg-orange';
                text = 'Faible';
            }

            strengthBar.className = 'progress-bar ' + color;
            strengthBar.style.width = width + '%';
            strengthText.textContent = pass ? 'Force : ' + text : '';
        }

        function checkPasswords() {
            if (password.value === '' && confirmPassword.value === '') {
                matchMessage.textContent = '';
                confirmPassword.classList.remove('is-invalid', 'is-valid');
                return;
            }

            if (password.value !== confirmPassword.value) {
                matchMessage.textContent = 'Les mots de passe ne correspondent pas.';
                matchMessage.classList.add('text-danger');
                matchMessage.classList.remove('text-success');
                confirmPassword.classList.add('is-invalid');
                confirmPassword.classList.remove('is-valid');
            } else {
                matchMessage.textContent = 'Les mots de passe correspondent.';
                matchMessage.classList.add('text-success');
                matchMessage.classList.remove('text-danger');
                confirmPassword.classList.add('is-valid');
                confirmPassword.classList.remove('is-invalid');
            }
        }

        function validatePasswordStrength(pass) {
            const regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            return regex.test(pass);
        }

        password.addEventListener('keyup', () => {
            updateStrengthMeter();
            checkPasswords();
        });
        confirmPassword.addEventListener('keyup', checkPasswords);

        form.addEventListener('submit', function(e) {
            // Si aucun mot de passe n’est saisi, on laisse passer
            if (password.value === '' && confirmPassword.value === '') {
                return;
            }

            if (!validatePasswordStrength(password.value)) {
                e.preventDefault();
                alert("Le mot de passe n'est pas assez sécurisé !");
                password.focus();
            } else if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert("Les mots de passe ne correspondent pas !");
                confirmPassword.focus();
            }
        });
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>