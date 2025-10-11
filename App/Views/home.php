<?php include __DIR__ . '/layouts/header.php'; ?>

<div class="container my-5">

    <!-- Section Héros -->
    <section class="hero-section text-center mb-5">
        <div class="row align-items-center g-4">
            <div class="col-md-6 text-start">
                <h1 class="display-5 text-primary fw-bold mb-3">Bienvenue au Cabinet du Dr. Dupont</h1>
                <p class="lead text-secondary">
                    Spécialiste en chirurgie dentaire, le <strong>Dr. Jean Dupont</strong> et son équipe vous accueillent
                    dans un cadre moderne et bienveillant pour des soins de qualité, en toute sérénité.
                </p>
                <a href="index.php?page=create_rdv" class="btn btn-lg btn-primary mt-3 btn-custom shadow-sm">
                    <i class="bi bi-calendar-check"></i> Prendre rendez-vous
                </a>
            </div>
            <div class="col-md-6 text-center">
                <img src="<?= BASE_URL ?>images/cabinet_dentiste_dr_dupont.jpg"
                    alt="Cabinet du Dr Dupont" class="img-fluid shadow-lg">
            </div>
        </div>
    </section>

    <!-- À propos -->
    <section class="mb-5">
        <h2 class="text-primary mb-4"><i class="bi bi-person-lines-fill"></i> À propos du Dr. Dupont</h2>
        <div class="row align-items-center">
            <div class="col-md-4 text-center mb-3 mb-md-0">
                <img src="<?= BASE_URL ?>images/dr-dupont.jpg"
                    alt="Dr Dupont" class="img-fluid team-photo">
            </div>
            <div class="col-md-8">
                <p>
                    Fort de plus de 15 ans d'expérience, le <strong>Dr. Jean Dupont</strong> met à votre service son savoir-faire et son écoute pour
                    garantir un accompagnement dentaire complet : de la prévention aux soins esthétiques, dans un environnement rassurant.
                </p>
                <p>
                    Notre équipe s'engage à offrir des traitements personnalisés et à utiliser les dernières technologies pour
                    le confort et la santé de nos patients.
                </p>
            </div>
        </div>
    </section>

    <!-- Nos Services Dynamiques -->
    <section class="mb-5">
        <h2 class="text-primary mb-4 text-center"><i class="bi bi-tooth"></i> Nos Services</h2>

        <?php if (!empty($services)): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($services as $service): ?>
                    <?php if ($service->isActive()): ?>
                        <div class="col">
                            <div class="card service-card h-100">
                                <img src="<?= BASE_URL ?>uploads/services/<?= htmlspecialchars($service->getImage()); ?>"
                                    alt="<?= htmlspecialchars($service->getNom()); ?>"
                                    class="card-img-top">

                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($service->getNom()); ?></h5>
                                    <p class="card-text"><?= $service->getDescription(); ?></p>
                                </div>

                                <div class="card-footer bg-transparent text-center border-0 pb-3">
                                    <a href="index.php?page=service_show&id=<?= $service->getId() ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-info-circle"></i> Voir le service
                                    </a><br>
                                    <a href="index.php?page=create_rdv&service_id=<?= $service->getId() ?>" class="btn btn-primary">
                                        <i class="bi bi-calendar-check"></i> Prendre rendez-vous
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <p class="text-center text-muted">Aucun service actif pour le moment.</p>
        <?php endif; ?>
    </section>

    <!-- Horaires -->
    <section class="mb-5">
        <h2 class="text-primary mb-4"><i class="bi bi-clock"></i> Horaires d'ouverture</h2>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle shadow-sm">
                <thead class="table-light">
                    <tr>
                        <th>Jour</th>
                        <th>Horaires</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horaires as $jour => $plages): ?>
                        <tr class="<?= empty($plages) ? 'table-secondary' : '' ?>">
                            <td><?= ucfirst(strtolower($jour)) ?></td>
                            <?php if (!empty($plages)): ?>
                                <td>
                                    <?php foreach ($plages as $p): ?>
                                        <div><?= htmlspecialchars($p['open']) ?> - <?= htmlspecialchars($p['close']) ?></div>
                                    <?php endforeach; ?>
                                </td>
                            <?php else: ?>
                                <td>Fermé</td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Call-to-action final -->
    <section class="text-center mt-5 bg-light py-5 rounded shadow-sm">
        <h4 class="text-primary mb-3">Un sourire en bonne santé commence ici</h4>
        <a href="index.php?page=create_rdv" class="btn btn-primary btn-lg btn-custom shadow-sm">
            <i class="bi bi-calendar2-heart"></i> Prendre rendez-vous maintenant
        </a>
    </section>

</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>