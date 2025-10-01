<?php
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../App/Models/RdvManager.php';
require_once __DIR__ . '/../App/Controllers/RDVController.php';
require_once __DIR__ . '/../App/Models/UserManager.php';
require_once __DIR__ . '/../App/Models/ServiceManager.php';
require_once __DIR__ . '/../App/Models/DisponibiliteStaffManager.php';
require_once __DIR__ . '/../App/Models/DisponibiliteServiceManager.php';
require_once __DIR__ . '/../App/Models/Rdv.php';
require_once __DIR__ . '/../App/Models/Service.php';
require_once __DIR__ . '/../App/Models/User.php';
require_once __DIR__ . '/../App/Models/DisponibiliteStaff.php';
require_once __DIR__ . '/../App/Models/DisponibiliteService.php';
require_once __DIR__ . '/../Public/helpers.php';


// === Connexion à la BDD ===
$config = require __DIR__ . '/../Config/config.php';
$pdo = (new Database($config))->getConnection();

// === Instancier le controller ===
$controller = new RDVController($pdo, $config);

// === Paramètres de test ===
// Choisis un staff (médecin) et un service qui existent bien dans ta BDD
$staffId   = 3; // ex: médecin ID 3
$serviceId = 1; // ex: service dentiste ID 1

// Semaine actuelle
$startOfWeek = new DateTimeImmutable("monday this week");
$datesSemaine = [];
for ($i = 0; $i < 7; $i++) {
    $datesSemaine[] = $startOfWeek->modify("+{$i} days");
}

// === Exécuter la méthode ===
$slots = (new ReflectionClass($controller))
    ->getMethod('generateWeekSlots')
    ->invoke($controller, $staffId, $serviceId, $datesSemaine);

// === Affichage ===
echo "=== TEST generateWeekSlots ===\n";
foreach ($slots as $heure => $jours) {
    foreach ($jours as $date => $slot) {
        $color = $slot['disponible'] ? '🟢' : '🔴';
        echo "{$date} {$heure} : {$color}\n";
    }
}
