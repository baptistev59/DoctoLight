<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>DoctoLight</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>styles.css">
    <!-- Place the first <script> tag in your HTML's <head> -->
    <script src="https://cdn.tiny.cloud/1/v7mkx9uc5az8ogwj4gkxn3hjg3jqs2wiqxauqdkwuyrpf7qj/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

    <!-- Place the following <script> and <textarea> tags your HTML's <body> -->
    <script>
        tinymce.init({
            selector: 'textarea',
            plugins: [
                // Core editing features
                'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
                // Your account includes a free trial of TinyMCE premium features
                // Try the most popular premium features until Oct 13, 2025:
                'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'advtemplate', 'ai', 'uploadcare', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown', 'importword', 'exportword', 'exportpdf'
            ],
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            tinycomments_mode: 'embedded',
            tinycomments_author: 'Author name',
            mergetags_list: [{
                    value: 'First.Name',
                    title: 'First Name'
                },
                {
                    value: 'Email',
                    title: 'Email'
                },
            ],
            ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
            uploadcare_public_key: '28bf5d29734ad322e374',
            setup: function(editor) {
                // Avant la soumission du formulaire, on force la sauvegarde
                editor.on('change', function() {
                    tinymce.triggerSave();
                });
            }
        });
    </script>
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
                    'ADMIN'      => [
                        'Administration' => BASE_URL . 'index.php?page=users',
                    ],
                    'SECRETAIRE' => [
                        'Tableau de bord' => BASE_URL . 'index.php?page=dashboard',
                        'Prendre RDV'     => BASE_URL . 'index.php?page=create_rdv'
                    ],
                    'MEDECIN'    => ['Tableau de bord' => BASE_URL . 'index.php?page=dashboard'],
                    'PATIENT'    => ['Prendre RDV' => BASE_URL . 'index.php?page=create_rdv'],
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