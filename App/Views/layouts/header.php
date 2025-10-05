<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>DoctoLight</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- TinyMCE -->

</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- ================= HEADER ================= -->
    <header class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="<?= BASE_URL ?>index.php">
                <i class="bi bi-hospital me-1"></i> DoctoLight
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>index.php?page=news">Actualités</a></li>

                    <?php
                    $currentUser = $_SESSION['user'] ?? null;
                    $currentRoles = $currentUser instanceof User ? $currentUser->getRoles() : [];
                    ?>

                    <?php if ($currentUser instanceof User): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <?= htmlspecialchars($currentUser->getPrenom() . " " . $currentUser->getNom()) ?>
                                (<?= htmlspecialchars($currentUser->getHighestRole() ?? '') ?>)
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>index.php?page=profile"><i class="bi bi-person-circle me-1"></i> Profil</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>index.php?page=logout"><i class="bi bi-box-arrow-right me-1"></i> Déconnexion</a></li>
                            </ul>
                        </li>

                        <?php
                        // Liens par rôle
                        $menuLinks = [
                            'ADMIN' => [
                                'Administration'   => BASE_URL . 'index.php?page=users',
                                'Services'         => BASE_URL . 'index.php?page=services',
                            ],
                            'SECRETAIRE' => [
                                'Tableau de bord'  => BASE_URL . 'index.php?page=dashboard',
                                'Prendre RDV'      => BASE_URL . 'index.php?page=create_rdv',
                                'Liste RDV'        => BASE_URL . 'index.php?page=rdv',
                                'Services'         => BASE_URL . 'index.php?page=services',
                            ],
                            'MEDECIN' => [
                                'Tableau de bord'  => BASE_URL . 'index.php?page=dashboard',
                                'Liste RDV'        => BASE_URL . 'index.php?page=rdv',
                            ],
                            'PATIENT' => [
                                'Prendre RDV'      => BASE_URL . 'index.php?page=create_rdv',
                                'Mes RDV'          => BASE_URL . 'index.php?page=rdv_listpatient',
                            ],
                        ];

                        $displayed = [];
                        foreach ($currentRoles as $role) {
                            $roleName = is_string($role) ? $role : $role->getName();
                            if (!empty($menuLinks[$roleName])) {
                                foreach ($menuLinks[$roleName] as $label => $url) {
                                    if (!in_array($label, $displayed, true)) {
                                        echo '<li class="nav-item"><a class="nav-link text-white" href="' . $url . '">' . $label . '</a></li>';
                                        $displayed[] = $label;
                                    }
                                }
                            }
                        }
                        ?>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>index.php?page=login"><i class="bi bi-box-arrow-in-right me-1"></i> Connexion</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>index.php?page=register"><i class="bi bi-person-plus me-1"></i> Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </header>

    <!-- ================= MAIN ================= -->
    <main class="container flex-grow-1 py-5">