<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>DoctoLight</title>
    <link rel="stylesheet" href="/styles.css">
</head>

<body>
    <header>
        <h1>DoctoLight</h1>

        <?php
        $user = $_SESSION['user'] ?? null;
        $roles = $user instanceof User ? $user->getRoles() : [];
        ?>

        <?php if ($user instanceof User): ?>
            <p>
                Bonjour,
                <strong><?= htmlspecialchars($user->getPrenom() . " " . $user->getNom()) ?></strong>
                (<?= htmlspecialchars($user->getHighestRole() ?? '') ?>)
            </p>
        <?php endif; ?>

        <nav>
            <a href="<?= BASE_URL ?>index.php">Accueil</a> |
            <a href="<?= BASE_URL ?>index.php?page=news">Actualités</a>

            <?php if ($user instanceof User): ?>
                | <a href="/index.php?page=logout">Déconnexion</a>

                <?php
                // Définition des liens par rôle
                $menuLinks = [
                    'ADMIN'      => ['Administration' => BASE_URL . '/index.php?page=users'],
                    'SECRETAIRE' => ['Tableau de bord' => BASE_URL . '/index.php?page=dashboard'],
                    'MEDECIN'    => ['Tableau de bord' => BASE_URL . '/index.php?page=dashboard'],
                    'PATIENT'    => ['Prendre RDV' => BASE_URL . '/index.php?page=rdv'],
                ];

                // Parcours tous les rôles de l'utilisateur et affiche les liens uniques
                $displayed = [];
                foreach ($roles as $role) {
                    if (!empty($menuLinks[$role])) {
                        foreach ($menuLinks[$role] as $label => $url) {
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