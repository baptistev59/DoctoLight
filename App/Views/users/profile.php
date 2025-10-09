<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4 text-primary">
        <i class="bi bi-person-circle"></i> Fiche de l'utilisateur
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
    ?>

    <!-- Carte d'informations -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3 text-primary">
                <?= htmlspecialchars($userToView->getPrenom() . ' ' . strtoupper($userToView->getNom())) ?>
            </h5>

            <p><strong>Email :</strong> <?= htmlspecialchars($userToView->getEmail()) ?></p>
            <p><strong>Date de naissance :</strong> <?= htmlspecialchars($userToView->getDateNaissance() ?? '-') ?></p>

            <?php if ($isAdminOrStaff): ?>
                <p><strong>Actif :</strong>
                    <?php if ($userToView->isActive()): ?>
                        <span class="badge bg-success">Oui</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Non</span>
                    <?php endif; ?>
                </p>

                <p><strong>Rôles :</strong>
                    <?php foreach ($userToView->getRoles() as $r): ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars($r->getName()) ?></span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sections selon les rôles -->
    <?php if ($userToView->hasRole('PATIENT')): ?>
        <div class="card mb-3 border-info">
            <div class="card-header bg-info-subtle text-info fw-semibold">
                <i class="bi bi-heart-pulse"></i> Informations Patient
            </div>
            <div class="card-body">
                <p>Dossiers médicaux, historique des rendez-vous, prescriptions, etc.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($userToView->hasRole('MEDECIN')): ?>
        <div class="card mb-3 border-success">
            <div class="card-header bg-success-subtle text-success fw-semibold">
                <i class="bi bi-stethoscope"></i> Informations Médecin
            </div>
            <div class="card-body">
                <p>Planning personnel, rendez-vous à venir, statistiques des consultations.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($userToView->hasRole('SECRETAIRE')): ?>
        <div class="card mb-3 border-warning">
            <div class="card-header bg-warning-subtle text-warning fw-semibold">
                <i class="bi bi-telephone"></i> Informations Secrétaire
            </div>
            <div class="card-body">
                <p>Tableau de bord, gestion des rendez-vous et communication avec les patients.</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($userToView->hasRole('ADMIN')): ?>
        <div class="card mb-3 border-danger">
            <div class="card-header bg-danger-subtle text-danger fw-semibold">
                <i class="bi bi-gear"></i> Informations Administrateur
            </div>
            <div class="card-body">
                <p>Accès complet au système, gestion des utilisateurs et des rôles.</p>
            </div>
        </div>
    <?php endif; ?>


    <!-- Boutons d'action -->
    <div class="mt-4">
        <?php if ($currentUser && $currentUser->getId() === $userToView->getId()): ?>
            <a href="<?= BASE_URL ?>index.php?page=users_edit&id=<?= $userToView->getId() ?>"
                class="btn btn-primary">
                <i class="bi bi-pencil"></i> Modifier mon profil
            </a>
        <?php elseif ($isAdminOrStaff): ?>
            <a href="<?= BASE_URL ?>index.php?page=users_edit&id=<?= $userToView->getId() ?>"
                class="btn btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Éditer
            </a>
        <?php endif; ?>

        <?php if ($currentUser && $currentUser->hasRole('ADMIN')): ?>
            <a href="<?= BASE_URL ?>index.php?page=users" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-arrow-left"></i> Retour à la liste
            </a>
        <?php endif; ?>

        <?php if ($currentUser && $currentUser->hasRole('ADMIN') && $userToView->getId() !== $currentUser->getId()): ?>
            <form method="POST"
                action="<?= BASE_URL ?>index.php?page=users_toggle&id=<?= $userToView->getId() ?>"
                class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="id" value="<?= $userToView->getId() ?>">
                <button type="submit"
                    class="btn btn-outline-<?= $userToView->isActive() ? 'danger' : 'success' ?> ms-2"
                    onclick="return confirm('Voulez-vous vraiment <?= $userToView->isActive() ? 'désactiver' : 'activer' ?> cet utilisateur ?');">
                    <i class="bi <?= $userToView->isActive() ? 'bi-person-x' : 'bi-person-check' ?>"></i>
                    <?= $userToView->isActive() ? 'Désactiver' : 'Activer' ?>
                </button>
            </form>
        <?php endif; ?>

    </div>
    <?php if ($userToView->hasRole(['MEDECIN'])): ?>
        <?php
        $staff = $userToView;
        include __DIR__ . '/../disponibilites/_staff_list.php';
        ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>