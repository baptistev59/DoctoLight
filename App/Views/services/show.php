<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5">
    <div class="card shadow-sm">
        <div class="row g-0">
            <!-- Image du service -->
            <?php if ($service->getImage()): ?>
                <div class="col-md-5">
                    <img src="<?= BASE_URL ?>uploads/services/<?= htmlspecialchars($service->getImage()) ?>"
                        alt="<?= htmlspecialchars($service->getNom()) ?>"
                        class="img-fluid rounded-start w-100 h-100 object-fit-cover">
                </div>
            <?php endif; ?>

            <div class="col-md-7">
                <div class="card-body">
                    <h2 class="card-title text-primary mb-3">
                        <?= htmlspecialchars($service->getNom()) ?>
                    </h2>

                    <?php if ($service->getDescription()): ?>
                        <p class="card-text"><?= nl2br($service->getDescription()) ?></p>
                    <?php endif; ?>

                    <p class="card-text">
                        <strong>Durée moyenne :</strong> <?= htmlspecialchars($service->getDuree()) ?> minutes
                    </p>

                    <div class="d-flex gap-2 mt-4">
                        <!-- Bouton de prise de rendez-vous -->
                        <a href="index.php?page=create_rdv&service_id=<?= $service->getId() ?>"
                            class="btn btn-outline-primary">
                            <i class="bi bi-calendar-plus"></i> Prendre rendez-vous
                        </a>

                        <!-- Boutons admin/secrétaire -->
                        <?php
                        if ($currentUser && $currentUser->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
                            <a href="index.php?page=services_edit&id=<?= $service->getId() ?>"
                                class="btn btn-warning">
                                <i class="bi bi-pencil-square"></i> Modifier
                            </a>

                            <form action="index.php?page=services_delete&id=<?= $service->getId() ?>"
                                method="post" style="display:inline;">
                                <input type="hidden" name="csrf_token"
                                    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <button type="submit"
                                    class="btn btn-danger"
                                    onclick="return confirm('Voulez-vous vraiment supprimer ce service ?');">
                                    <i class="bi bi-trash"></i> Supprimer
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <a href="index.php?page=home" class="btn btn-outline-secondary mt-3">
                        <i class="bi bi-arrow-left"></i> Retour à l'accueil
                    </a>
                </div>
                <?php include __DIR__ . '/../disponibilites/_service_list.php'; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>