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

        <form id="registerForm" method="post" action="index.php?page=register">
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
                <input type="password" name="password_confirm" id="password_confirm" class="form-control"
                    placeholder="••••••••" required>
                <div id="matchMessage" class="form-text"></div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirm');
        const matchMessage = document.getElementById('matchMessage');
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('passwordStrengthText');

        // Calcul de la force du mot de passe
        function evaluateStrength(pass) {
            let score = 0;
            if (pass.length >= 8) score++;
            if (/[A-Z]/.test(pass)) score++;
            if (/[a-z]/.test(pass)) score++;
            if (/\d/.test(pass)) score++;
            if (/[@$!%*?&]/.test(pass)) score++;
            return score;
        }

        // Mise à jour de la barre
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

        // Vérifie la correspondance
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

        // Vérifie la sécurité minimale avant envoi
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