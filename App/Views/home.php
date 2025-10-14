<?php include __DIR__ . '/layouts/header.php'; ?>

<div class="container my-5">

    <!-- Section Héros -->
    <section class="hero-section mb-5">
        <div class="row align-items-center g-4">
            <div class="col-12 col-md-6 order-1 order-md-2 text-center">
                <img src="<?= BASE_URL ?>images/cabinet_dentiste_dr_dupont.jpg"
                    alt="Cabinet du Dr Dupont" class="img-fluid rounded shadow">
            </div>
            <div class="col-12 col-md-6 order-2 order-md-1 text-start">
                <h1 class="display-5 text-primary fw-bold mb-3">Bienvenue au Cabinet du Dr. Dupont</h1>
                <p class="lead text-secondary">
                    Spécialiste en chirurgie dentaire, le <strong>Dr. Jean Dupont</strong> et son équipe vous accueillent
                    dans un cadre moderne et bienveillant pour des soins de qualité, en toute sérénité.
                </p>
                <a href="index.php?page=create_rdv" class="btn btn-lg btn-primary mt-3">
                    <i class="bi bi-calendar-check"></i> Prendre rendez-vous
                </a>
            </div>

        </div>
    </section>

    <!-- À propos -->
    <section class="mb-5 about-section">
        <h2 class="text-primary mb-4 text-center">
            <i class="bi bi-person-lines-fill"></i> À propos du Dr. Dupont
        </h2>
        <div class="row align-items-center g-4">
            <div class="col-12 col-md-4 order-1 order-md-1 text-center">
                <img src="<?= BASE_URL ?>images/dr-dupont.jpg"
                    alt="Dr Dupont" class="img-fluid rounded-circle shadow">
            </div>
            <div class="col-12 col-md-8 order-2 order-md-2">
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
    <!-- Séparation visuelle -->
    <hr class="my-5">
    <!-- Dernières actualités -->
    <?php if (!empty($latestNews)): ?>
        <section class="mb-5 news-section">
            <h2 class="text-primary mb-4 text-center">
                <i class="bi bi-newspaper"></i> Dernières actualités
            </h2>

            <div class="row g-4">
                <?php foreach ($latestNews as $n): ?>
                    <div class="col-12 col-md-6 col-lg-4 animate-fadeInUp">
                        <div class="card h-100 border-0 shadow-sm card-base">
                            <?php if ($n->getImage()): ?>
                                <img src="<?= BASE_URL ?>uploads/news/<?= htmlspecialchars($n->getImage()) ?>"
                                    class="card-img-top" alt="<?= htmlspecialchars($n->getTitre()) ?>">
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title text-primary fw-bold">
                                    <?= htmlspecialchars($n->getTitre()) ?>
                                </h5>
                                <p class="card-text text-muted">
                                    <?= nl2br(htmlspecialchars(substr($n->getContenu(), 0, 100))) ?>...
                                </p>
                            </div>

                            <div class="card-footer bg-transparent border-0 text-center">
                                <a href="index.php?page=news_show&id=<?= $n->getId() ?>" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-eye"></i> Lire la suite
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="index.php?page=news" class="btn btn-primary">
                    <i class="bi bi-collection"></i> Voir toutes les actualités
                </a>
            </div>
        </section>
    <?php endif; ?>
    <!-- Séparation visuelle -->
    <hr class="my-5">

    <!-- Nos Services Dynamiques -->
    <?php if (!empty($services)): ?>
        <div class="row g-4">
            <?php foreach ($services as $service): ?>
                <?php if ($service->isActive()): ?>
                    <!-- 1 colonne mobile, 2 en ≥ md, 3 en ≥ lg -->
                    <div class="col-12 col-md-6 col-lg-4 animate-fadeInUp">
                        <div class="card h-100 border-0 shadow-sm card-base">
                            <img src="<?= BASE_URL ?>uploads/services/<?= htmlspecialchars($service->getImage()) ?>"
                                class="card-img-top"
                                alt="<?= htmlspecialchars($service->getNom()) ?>">

                            <div class="card-body text-center">
                                <h5 class="card-title text-primary fw-bold">
                                    <?= htmlspecialchars($service->getNom()) ?>
                                </h5>
                                <p class="card-text text-muted">
                                    <?= nl2br(htmlspecialchars($service->getDescription())) ?>
                                </p>
                            </div>

                            <div class="card-footer bg-transparent border-0 d-flex flex-column justify-content-center gap-2">
                                <a href="index.php?page=service_show&id=<?= $service->getId() ?>" class="btn btn-outline-primary w-100 w-md-auto">
                                    <i class="bi bi-info-circle"></i> Voir le service
                                </a>
                                <a href="index.php?page=create_rdv&service_id=<?= $service->getId() ?>" class="btn btn-primary w-100 w-md-auto">
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
    <!-- Séparation visuelle -->
    <hr class="my-5">

    <!-- Fermetures exceptionnelles -->
    <?php if (!empty($fermeturesActives)): ?>
        <div class="alert alert-warning shadow-sm animate-fadeInUp">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Fermeture exceptionnelle :</strong>
            <?php foreach ($fermeturesActives as $f): ?>
                <div>
                    Du <?= htmlspecialchars($f['date_debut']) ?> au <?= htmlspecialchars($f['date_fin']) ?>
                    <?= !empty($f['motif']) ? ' — ' . htmlspecialchars($f['motif']) : '' ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Horaires d'ouverture -->
    <section class="mb-5 schedule-section">
        <h2><i class="bi bi-clock"></i> Horaires d'ouverture</h2>
        <div class="table-responsive animate-fadeInUp">
            <table class="table table-bordered text-center align-middle shadow-sm">
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Horaires</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- <?php var_dump($horaires); ?> -->
                    <?php foreach ($horaires as $jour => $plages): ?>
                        <tr class="<?= empty($plages['open']) ? 'table-secondary' : '' ?>">
                            <td data-label="Jour"><?= ucfirst(strtolower($jour)) ?></td>
                            <td data-label="Horaires">
                                <?php if (!empty($plages['open']) && !empty($plages['close'])): ?>
                                    <?= htmlspecialchars($plages['open']) ?> - <?= htmlspecialchars($plages['close']) ?>
                                <?php else: ?>
                                    Fermé
                                <?php endif; ?>
                            </td>
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