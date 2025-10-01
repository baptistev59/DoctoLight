<?php
// Charger la config
$config = require __DIR__ . '/../Config/config.php';

// Charger les classes nécessaires
require_once __DIR__ . '/../App/Models/RdvManager.php';

// Connexion à la BDD
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['db_name']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Instancier RdvManager
$rdvManager = new RdvManager($pdo);

// ===================
// TESTS
// ===================
echo "=== TEST findConflict ===\n";

// Test 1 : staff 2, RDV à 09:00 pour 30min
$start = new DateTime('2025-10-02 09:00:00');
echo "Staff 2 @ 09:00 : " .
    ($rdvManager->findConflict(2, $start, 30, 'staff') ? "CONFLIT\n" : "DISPO\n");

// Test 2 : patient 5, RDV à 09:00 pour 30min
echo "Patient 5 @ 09:00 : " .
    ($rdvManager->findConflict(5, $start, 30, 'patient') ? "CONFLIT\n" : "DISPO\n");

// Test 3 : staff 2, RDV à 10:00 pour 30min
$start = new DateTime('2025-10-02 10:00:00');
echo "Staff 2 @ 10:00 : " .
    ($rdvManager->findConflict(2, $start, 30, 'staff') ? "CONFLIT\n" : "DISPO\n");

// Test 4 : patient 5, RDV à 10:00 pour 30min
echo "Patient 5 @ 10:00 : " .
    ($rdvManager->findConflict(5, $start, 30, 'patient') ? "CONFLIT\n" : "DISPO\n");
