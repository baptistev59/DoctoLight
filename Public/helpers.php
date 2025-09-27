
<?php
// fonctions utilitaires globales

/**
 * Redirection vers une autre page
 */
function redirect(string $url): void
{
    header("Location: " . $url);
    exit;
}

/**
 * Charger une vue depuis App/Views/
 */
function view(string $path, array $data = []): void
{
    extract($data); // rend les variables disponibles dans la vue
    $file = __DIR__ . "/../App/Views/{$path}.php";
    if (file_exists($file)) {
        include $file;
    } else {
        echo "Vue introuvable : {$path}";
    }
}

/**
 * Générer l’URL absolue vers un asset (css, image…)
 * Exemple : <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
 */
function asset(string $path): string
{
    return BASE_URL . "Public/" . ltrim($path, '/');
}

/**
 * Vérifie si un utilisateur est connecté
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

/**
 * Vérifie si l’utilisateur a un rôle donné
 * Exemple : hasRole('admin')
 */
function hasRole(string $role): bool
{
    return isset($_SESSION['user']['roles']) && in_array($role, $_SESSION['user']['roles']);
}

/**
 * Test de redirection
 */
if (!defined('BASE_URL')) {
    define('BASE_URL', $config['base_url']);
}
require_once __DIR__ . '/helpers.php';
