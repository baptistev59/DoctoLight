<?php include __DIR__ . '/layouts/header.php'; ?>

<div class="container my-5">

    <!-- Héros -->
    <section class="hero-about text-center mb-5">
        <img src="<?= BASE_URL ?>images/dr-dupont.jpg" alt="Dr Jean Dupont" class="img-fluid team-photo">
        <h1 class="display-5 text-primary fw-bold mb-3">Dr Jean Dupont</h1>
        <p class="lead text-secondary mb-3">Chirurgien-dentiste - Expert en soins esthétiques et implantologie</p>
        <p class="text-muted">
            "Parce que chaque sourire est unique, je m'engage à offrir des soins personnalisés,
            innovants et respectueux de votre bien-être."
        </p>
    </section>

    <!-- Parcours -->
    <section class="mb-5">
        <h2 class="section-title"><i class="bi bi-mortarboard"></i> Parcours & Formation</h2>
        <p>
            Diplômé de la Faculté d'Odontologie de Paris en 2018, le <strong>Dr. Jean Dupont</strong> a complété plusieurs formations spécialisées
            en <strong>implantologie dentaire</strong> et en <strong>esthétique du sourire</strong>.
        </p>
        <ul>
            <li>📘 Doctorat en chirurgie dentaire - Université Paris VII</li>
            <li>🏅 Diplôme universitaire d'Implantologie - Université de Montpellier</li>
            <li>🎓 Formation continue en esthétique dentaire et facettes céramiques</li>
        </ul>
        <p>
            Avec plus de 10 ans d'expérience, le Dr. Dupont a su allier expertise technique et approche humaine
            pour accompagner ses patients avec rigueur et bienveillance.
        </p>
    </section>

    <!-- Philosophie -->
    <section class="mb-5">
        <h2 class="section-title"><i class="bi bi-heart-pulse"></i> Notre philosophie</h2>
        <p>
            Notre cabinet repose sur des valeurs simples :
        </p>
        <ul>
            <li>👩‍⚕️ L'écoute attentive du patient et de ses besoins</li>
            <li>💎 Des soins modernes basés sur des technologies de pointe</li>
            <li>🤝 Une relation de confiance et un suivi personnalisé</li>
        </ul>
        <p>
            Nous croyons qu'un beau sourire commence par une approche globale de la santé bucco-dentaire.
        </p>
    </section>

    <!-- Équipe -->
    <section class="mb-5">
        <h2 class="section-title text-center"><i class="bi bi-people"></i> L'équipe du cabinet</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4 text-center">
            <div class="col">
                <div class="card team-card h-100">
                    <img src="<?= BASE_URL ?>images/dr-claire.jpg" alt="Dr Claire Durand - Chirurgienne-dentiste — Spécialiste en dentisterie esthétique">
                    <div class="card-body">
                        <h5 class="card-title">Dr Claire Durand</h5>
                        <p class="text-muted">Chirurgienne-dentiste — Spécialiste en dentisterie esthétique</p>
                        <p>
                            Experte en <strong>esthétique du sourire</strong> et en <strong>facettes céramiques</strong>,
                            le Dr Durand accompagne ses patients dans la restauration harmonieuse et naturelle de leur dentition.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card team-card h-100">
                    <img src="<?= BASE_URL ?>images/secretaire-marie.jpg" alt="Marie Dupont - Secrétaire médicale">
                    <div class="card-body">
                        <h5 class="card-title">Marie Dupont</h5>
                        <p class="text-muted">Secrétaire médicale</p>
                        <p>Votre premier contact au cabinet, toujours à l'écoute pour planifier vos rendez-vous.</p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card team-card h-100">
                    <img src="<?= BASE_URL ?>images/docteur-luc.jpg" alt="Dr Luc Bernard - Chirurgien-dentiste — Spécialiste en implantologie et prothèses">
                    <div class="card-body">
                        <h5 class="card-title">Dr Luc Bernard</h5>
                        <p class="text-muted">Chirurgien-dentiste — Spécialiste en implantologie et prothèses</p>
                        <p>
                            Le Dr Bernard est reconnu pour son savoir-faire en <strong>implantologie dentaire</strong> et
                            en <strong>réhabilitation complète du sourire</strong>, avec une approche douce et précise.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bouton retour -->
    <div class="text-center mt-5">
        <a href="index.php?page=home" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Retour à l'accueil
        </a>
    </div>

</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>