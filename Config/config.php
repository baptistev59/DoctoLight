<?php
$envFile = __DIR__ . '/.env';
$env = [];

// Lecture du fichier .env
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        // Ignorer les commentaires commençant par #
        if (strpos(trim($line), '#') === 0) continue;

        // Découpage clé=valeur
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim($value);
    }
}

return [
    'base_url'       => $env['BASE_URL'] ?? 'http://baptistev59.alwaysdata.net/doctolight/',
    'host'           => $env['DB_HOST'] ?? 'mysql-baptistev59.alwaysdata.net',
    'db_name'        => $env['DB_NAME'] ?? 'baptistev59_doctolight',
    'username'       => $env['DB_USER'] ?? '428185',
    'password'       => $env['DB_PASS'] ?? 'Teqapexa59!',
    'role_hierarchy' => explode(',', $env['ROLE_HIERARCHY'] ?? 'ADMIN,SECRETAIRE,MEDECIN,PATIENT'),
];
