<?php include __DIR__ . '/layouts/header.php'; ?>

<div class="container my-5">

    <!-- Section Héros -->
    <section class="text-center mb-5">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-5 text-primary fw-bold mb-3">Bienvenue au Cabinet du Dr. Dupont</h1>
                <p class="lead text-secondary">
                    Spécialiste en chirurgie dentaire, le Dr. Dupont et son équipe vous accueillent dans un cadre moderne et convivial pour des soins de qualité.
                </p>
                <a href="index.php?page=create_rdv" class="btn btn-lg btn-primary mt-3">
                    <i class="bi bi-calendar-check"></i> Prendre rendez-vous
                </a>
            </div>
            <div class="col-md-6 text-center">
                <img src="<?= BASE_URL ?>images/cabinet_dentiste_dr_dupont.jpg" alt="Cabinet du Dr Dupont" class="img-fluid rounded shadow">
            </div>
        </div>
    </section>

    <hr class="my-5">

    <!-- Section Présentation -->
    <section class="mb-5">
        <h2 class="text-primary mb-4"><i class="bi bi-person-lines-fill"></i> À propos du Dr. Dupont</h2>
        <div class="row">
            <div class="col-md-4">
                <img src="<?= BASE_URL ?>images/dr-dupont.jpg" alt="Dr Dupont" class="img-fluid rounded-circle shadow-sm">
            </div>
            <div class="col-md-8">
                <p>
                    Le <strong>Dr. Jean Dupont</strong> exerce depuis plus de 15 ans et met son expertise au service de la santé bucco-dentaire de ses patients.
                    Son approche bienveillante et son matériel à la pointe de la technologie garantissent des soins de haute qualité dans un environnement rassurant.
                </p>
                <p>
                    Le cabinet propose une gamme complète de services : soins dentaires, blanchiment, implants, prothèses et suivi personnalisé pour chaque patient.
                </p>
            </div>
        </div>
    </section>

    <!-- Section Services -->
    <section class="mb-5">
        <h2 class="text-primary mb-4"><i class="bi bi-tooth"></i> Nos Services</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-emoji-smile display-5 text-success"></i>
                        <h5 class="card-title mt-3">Soins dentaires</h5>
                        <p class="card-text text-muted">Traitements complets pour préserver la santé de vos dents et de vos gencives.</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center">
                        <a href="index.php?page=create_rdv" class="btn btn-primary">
                            Prendre rendez-vous
                        </a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-brightness-high display-5 text-warning"></i>
                        <h5 class="card-title mt-3">Blanchiment</h5>
                        <p class="card-text text-muted">Retrouvez un sourire éclatant grâce à nos techniques de blanchiment modernes.</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center">
                        <a href="index.php?page=create_rdv" class="btn btn-primary">
                            Prendre rendez-vous
                        </a>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-tools display-5 text-info"></i>
                        <h5 class="card-title mt-3">Implants et prothèses</h5>
                        <p class="card-text text-muted">Solutions sur mesure pour remplacer une dent manquante ou abîmée.</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center">
                        <a href="index.php?page=create_rdv" class="btn btn-primary">
                            Prendre rendez-vous
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Horaires -->
    <section class="mb-5">
        <h2 class="text-primary mb-4"><i class="bi bi-clock"></i> Horaires d'ouverture</h2>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle shadow-sm">
                <thead class="table-light">
                    <tr>
                        <th>Jour</th>
                        <th>Matin</th>
                        <th>Après-midi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Lundi</td>
                        <td>08h30 - 12h30</td>
                        <td>14h00 - 18h00</td>
                    </tr>
                    <tr>
                        <td>Mardi</td>
                        <td>08h30 - 12h30</td>
                        <td>14h00 - 18h00</td>
                    </tr>
                    <tr>
                        <td>Mercredi</td>
                        <td>08h30 - 12h30</td>
                        <td>14h00 - 17h00</td>
                    </tr>
                    <tr>
                        <td>Jeudi</td>
                        <td>08h30 - 12h30</td>
                        <td>14h00 - 18h00</td>
                    </tr>
                    <tr>
                        <td>Vendredi</td>
                        <td>08h30 - 12h30</td>
                        <td>Fermé</td>
                    </tr>
                    <tr class="table-secondary">
                        <td>Samedi</td>
                        <td colspan="2">Fermé</td>
                    </tr>
                    <tr class="table-secondary">
                        <td>Dimanche</td>
                        <td colspan="2">Fermé</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Section contact / call to action -->
    <section class="text-center mt-5">
        <p class="lead mb-3">Prenez soin de votre sourire dès aujourd'hui !</p>
        <a href="index.php?page=create_rdv" class="btn btn-primary btn-lg">
            <i class="bi bi-calendar2-heart"></i> Prendre rendez-vous
        </a>
    </section>

</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>