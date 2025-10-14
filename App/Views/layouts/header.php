<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DoctoLight</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= BASE_URL ?>css/style.css" rel="stylesheet">

    <!-- Place the first <script> tag in your HTML's <head> -->
    <script src="https://cdn.tiny.cloud/1/v7mkx9uc5az8ogwj4gkxn3hjg3jqs2wiqxauqdkwuyrpf7qj/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: 'textarea.rich-text', // toutes les zones avec cette classe
                plugins: 'advlist autolink lists link image charmap preview anchor ' +
                    'searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                toolbar: 'undo redo | formatselect | bold italic backcolor | ' +
                    'alignleft aligncenter alignright alignjustify | ' +
                    'bullist numlist outdent indent | removeformat | help',
                menubar: false,
                height: 300,
                branding: false,
                language: 'fr_FR'
            });
        });
    </script>


</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- ================= HEADER ================= -->
    <header class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container py-2">
            <!-- Logo + Nom -->
            <a class="navbar-brand fw-bold text-white" href="<?= BASE_URL ?>index.php">
                <i class="bi bi-hospital me-1"></i> DoctoLight
            </a>

            <!-- Bouton mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu principal -->
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <!-- Liens publics -->
                    <li class="nav-item mx-lg-1">
                        <a class="nav-link text-white" href="<?= BASE_URL ?>index.php?page=home">Accueil</a>
                    </li>
                    <li class="nav-item mx-lg-1">
                        <a class="nav-link text-white" href="<?= BASE_URL ?>index.php?page=news">Actualités</a>
                    </li>

                    <?php
                    $currentUser = $_SESSION['user'] ?? null;
                    $currentRoles = $currentUser instanceof User ? $currentUser->getRoles() : [];
                    ?>

                    <?php if ($currentUser instanceof User): ?>
                        <!-- Menu utilisateur connecté -->
                        <li class="nav-item dropdown mx-lg-1">
                            <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1 fs-5"></i>
                                <span>
                                    <?= htmlspecialchars($currentUser->getPrenom() . " " . $currentUser->getNom()) ?>
                                </span>
                                <span class="ms-1 small text-light opacity-75">
                                    (<?= htmlspecialchars($currentUser->getHighestRole() ?? '') ?>)
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>index.php?page=profile">
                                        <i class="bi bi-gear me-2"></i> Profil
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= BASE_URL ?>index.php?page=logout">
                                        <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <?php
                        // Liens par rôle
                        $menuLinks = [
                            'ADMIN' => [
                                'Rendez-vous' => [
                                    'Planning des rendez-vous' => BASE_URL . 'index.php?page=rdv',
                                ],
                                'Administration' => [
                                    'Comptes des utilisateurs' => BASE_URL . 'index.php?page=users',
                                    'Services du cabinet' => BASE_URL . 'index.php?page=services',
                                    'Fermetures du cabinet' => BASE_URL . 'index.php?page=fermetures',
                                ],
                            ],
                            'SECRETAIRE' => [
                                'Rendez-vous' => [
                                    'Prendre un rendez-vous' => BASE_URL . 'index.php?page=create_rdv',
                                    'Planning des rendez-vous' => BASE_URL . 'index.php?page=rdv',
                                ],
                                'Administration' => [
                                    'Services du cabinet' => BASE_URL . 'index.php?page=services',
                                    'Fermetures du cabinet' => BASE_URL . 'index.php?page=fermetures',
                                ],
                            ],
                            'MEDECIN' => [
                                'Rendez-vous' => [
                                    'Planning des rendez-vous' => BASE_URL . 'index.php?page=rdv',
                                ],
                            ],
                            'PATIENT' => [
                                'Rendez-vous' => [
                                    'Prendre un rendez-vous' => BASE_URL . 'index.php?page=create_rdv',
                                    'Mes rendez-vous' => BASE_URL . 'index.php?page=rdv_listpatient',
                                ],
                            ],
                        ];


                        $displayed = [];
                        foreach ($currentRoles as $role) {
                            $roleName = is_string($role) ? $role : $role->getName();

                            if (!empty($menuLinks[$roleName])) {
                                foreach ($menuLinks[$roleName] as $label => $links) {
                                    // Si c'est un groupe de liens (menu déroulant)
                                    if (is_array($links)) {
                                        // Empêche de répéter le même dropdown
                                        if (in_array($label, $displayed, true)) continue;

                                        echo '<li class="nav-item dropdown">';
                                        echo '<a class="nav-link dropdown-toggle text-white" href="#" id="dropdown' . $label . '" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
                                        echo htmlspecialchars($label);
                                        echo '</a>';
                                        echo '<ul class="dropdown-menu shadow-sm">';
                                        foreach ($links as $label => $url) {
                                            echo '<li><a class="dropdown-item" href="' . $url . '">' . htmlspecialchars($label) . '</a></li>';
                                        }
                                        echo '</ul>';
                                        echo '</li>';

                                        $displayed[] = $label;
                                    }
                                    // Si c’est un lien direct
                                    else {
                                        if (!in_array($label, $displayed, true)) {
                                            echo '<li class="nav-item"><a class="nav-link text-white" href="' . $links  . '">' . htmlspecialchars($label) . '</a></li>';
                                            $displayed[] = $label;
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                        <li class="nav-item mx-lg-1">
                            <a class="nav-link text-white" href="<?= BASE_URL ?>index.php?page=apropos">À propos</a>
                        </li>
                    <?php else: ?>
                        <!-- Boutons Connexion / Inscription -->
                        <li class="nav-item  mx-lg-1">
                            <a class="btn btn-outline-light btn-sm px-3" href="<?= BASE_URL ?>index.php?page=login">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Connexion
                            </a>
                        </li>
                        <li class="nav-item mx-lg-1 mt-2 mt-lg-0">
                            <a class="btn btn-light btn-sm px-3" href="<?= BASE_URL ?>index.php?page=register">
                                <i class="bi bi-person-plus me-1"></i> Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <!-- ================= MAIN ================= -->
    <main class="container flex-grow-1 py-5">