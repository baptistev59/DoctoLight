<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">

    <h1 class="h3 mb-4 text-primary">
        <i class="bi bi-person-plus"></i> Créer un nouvel utilisateur
    </h1>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form id="userForm" method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

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
            <div id="passwordHelp" class="form-text text-muted">
                Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.
            </div>
            <div class="progress mt-2" style="height: 8px;">
                <div id="passwordStrengthBar" class="progress-bar bg-danger" style="width: 0%;"></div>
            </div>
            <small id="passwordStrengthText" class="form-text text-muted"></small>
        </div>

        <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
            <div id="matchMessage" class="form-text"></div>
        </div>

        <div class="mb-3">
            <label for="roles" class="form-label">Rôles</label>
            <div class="border rounded p-3">
                <?php foreach ($roles as $role): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="roles[]"
                            id="role_<?= htmlspecialchars($role->getName()) ?>"
                            value="<?= htmlspecialchars($role->getName()) ?>">
                        <label class="form-check-label" for="role_<?= htmlspecialchars($role->getName()) ?>">
                            <?= htmlspecialchars($role->getName()) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
            <label class="form-check-label" for="is_active">Activer le compte</label>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Enregistrer
        </button>
        <a href="<?= BASE_URL ?>index.php?page=users" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-arrow-left"></i> Annuler
        </a>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirm');
        const matchMessage = document.getElementById('matchMessage');
        const form = document.getElementById('userForm');
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('passwordStrengthText');

        // Fonction de calcul de la force
        function evaluateStrength(pass) {
            let score = 0;

            if (pass.length >= 8) score += 1;
            if (/[A-Z]/.test(pass)) score += 1;
            if (/[a-z]/.test(pass)) score += 1;
            if (/\d/.test(pass)) score += 1;
            if (/[@$!%*?&]/.test(pass)) score += 1;

            return score;
        }

        // Met à jour la barre et le texte
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

        // Vérification dynamique de correspondance
        function checkPasswords() {
            if (password.value === '' || confirmPassword.value === '') {
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

        // Validation finale
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
            if (!validatePasswordStrength(password.value)) {
                e.preventDefault();
                alert("Le mot de passe n’est pas assez sécurisé !");
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