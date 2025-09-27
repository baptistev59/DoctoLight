<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>DoctoLight</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>styles.css">
</head>

<body>
    <header>
        <h1>DoctoLight</h1>

        <?php
        // Utilisateur connecté
        $currentUser = $_SESSION['user'] ?? null;
        $currentRoles = $currentUser instanceof User ? $currentUser->getRoles() : [];
        ?>

        <?php if ($currentUser instanceof User): ?>
            <p>
                Bonjour,
                <strong><?= htmlspecialchars($currentUser->getPrenom() . " " . $currentUser->getNom()) ?></strong>
                (<?= htmlspecialchars($currentUser->getHighestRole() ?? '') ?>)
            </p>
        <?php endif; ?>

        <nav>
            <a href="<?= BASE_URL ?>index.php">Accueil</a> |
            <a href="<?= BASE_URL ?>index.php?page=news">Actualités</a>

            <?php if ($currentUser instanceof User): ?>
                | <a href="<?= BASE_URL ?>index.php?page=profile">Profil</a>
                | <a href="<?= BASE_URL ?>index.php?page=logout">Déconnexion</a>


                <?php
                // Définition des liens par rôle
                $menuLinks = [
                    'ADMIN'      => ['Administration' => BASE_URL . 'index.php?page=users'],
                    'SECRETAIRE' => ['Tableau de bord' => BASE_URL . 'index.php?page=dashboard'],
                    'MEDECIN'    => ['Tableau de bord' => BASE_URL . 'index.php?page=dashboard'],
                    'PATIENT'    => ['Prendre RDV' => BASE_URL . 'index.php?page=rdv'],
                ];

                // Parcours tous les rôles de l'utilisateur connecté et affiche les liens uniques
                $displayed = [];
                foreach ($currentRoles as $role) {
                    $roleName = is_string($role) ? $role : $role->getName();
                    if (!empty($menuLinks[$roleName])) {
                        foreach ($menuLinks[$roleName] as $label => $url) {
                            if (!in_array($label, $displayed, true)) {
                                echo " | <a href=\"{$url}\">{$label}</a>";
                                $displayed[] = $label;
                            }
                        }
                    }
                }
                ?>

            <?php else: ?>
                | <a href="<?= BASE_URL ?>index.php?page=login">Connexion</a>
                | <a href="<?= BASE_URL ?>index.php?page=register">Inscription</a>
            <?php endif; ?>
        </nav>
        <hr>
    </header>
    <main>