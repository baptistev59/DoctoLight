<?php
// CONFIG
$host = "mysql-baptistev59.alwaysdata.net";
$db   = "baptistev59_doctolight";
$user = "428185";
$pass = "Teqapexa59!";
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Connexion réussie\n";
} catch (Exception $e) {
    die("❌ Erreur connexion : " . $e->getMessage());
}

// --------- RESET DES TABLES ----------
$tables = ["audit_log", "rdv", "disponibilite_staff", "disponibilite_service", "user_roles", "roles", "users", "services", "news"];
foreach ($tables as $t) {
    $pdo->exec("DELETE FROM $t");
}
$pdo->exec("ALTER TABLE users AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE roles AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE services AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE disponibilite_staff AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE disponibilite_service AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE rdv AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE news AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE audit_log AUTO_INCREMENT = 1");

// --------- ROLES ----------
$roles = ["PATIENT", "SECRETAIRE", "MEDECIN", "ADMIN"];
$stmt = $pdo->prepare("INSERT INTO roles (name) VALUES (?)");
foreach ($roles as $r) {
    $stmt->execute([$r]);
}

// --------- USERS ----------
$users = [
    ["VANDAELE", "Baptiste", "baptistev59@free.fr", "123", "1983-01-01", "ADMIN"],
    ["Dupont", "Marie", "secretaire@test.fr", "123", "1990-05-10", "SECRETAIRE"],
    ["Martin", "Jean", "medecin1@test.fr", "123", "1975-03-20", "MEDECIN"],
    ["Durand", "Claire", "medecin2@test.fr", "123", "1980-07-12", "MEDECIN"],
    ["Bernard", "Luc", "medecin3@test.fr", "123", "1982-11-01", "MEDECIN"],
    ["Petit", "Sophie", "patient1@test.fr", "123", "1995-02-11", "PATIENT"],
    ["Leroy", "Paul", "patient2@test.fr", "123", "1998-08-25", "PATIENT"],
    ["Moreau", "Julie", "patient3@test.fr", "123", "2000-01-15", "PATIENT"],
    ["Fournier", "Marc", "patient4@test.fr", "123", "1992-04-30", "PATIENT"],
    ["Blanc", "Emma", "patient5@test.fr", "123", "1987-06-22", "PATIENT"]
];
$stmt = $pdo->prepare("INSERT INTO users (nom,prenom,email,password,date_naissance) VALUES (?,?,?,?,?)");
$user_roles = [];
foreach ($users as $i => $u) {
    $hash = password_hash($u[3], PASSWORD_BCRYPT);
    $stmt->execute([$u[0], $u[1], $u[2], $hash, $u[4]]);
    $userId = $pdo->lastInsertId();
    $user_roles[] = [$userId, $u[5]]; // role à affecter
}

// --------- USER_ROLES ----------
$getRoleId = $pdo->prepare("SELECT id FROM roles WHERE name=?");
$insRole   = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?,?)");
foreach ($user_roles as [$uid, $roleName]) {
    $getRoleId->execute([$roleName]);
    $roleId = $getRoleId->fetchColumn();
    $insRole->execute([$uid, $roleId]);
}

// --------- SERVICES ----------
$services = [
    ["Soins dentaires", 30],
    ["Orthodontie", 30],
    ["Implants dentaires", 45]
];
$stmt = $pdo->prepare("INSERT INTO services (nom,duree) VALUES (?,?)");
foreach ($services as $s) {
    $stmt->execute($s);
}

// --------- DISPONIBILITES STAFF ----------
$stmt = $pdo->prepare("INSERT INTO disponibilite_staff (user_id,jour_semaine,start_time,end_time) VALUES (?,?,?,?)");
$medecins = [3, 4, 5]; // IDs des médecins
$jours = ["LUNDI", "MARDI", "MERCREDI", "JEUDI", "VENDREDI"];
foreach ($medecins as $m) {
    foreach ($jours as $j) {
        $stmt->execute([$m, $j, "09:00:00", "12:00:00"]);
        $stmt->execute([$m, $j, "14:00:00", "18:00:00"]);
    }
}

// --------- DISPONIBILITES SERVICE ----------
$stmt = $pdo->prepare("INSERT INTO disponibilite_service (service_id,jour_semaine,start_time,end_time) VALUES (?,?,?,?)");
$serviceIds = [1, 2, 3];
foreach ($serviceIds as $s) {
    foreach ($jours as $j) {
        $stmt->execute([$s, $j, "09:00:00", "12:00:00"]);
        $stmt->execute([$s, $j, "14:00:00", "18:00:00"]);
    }
}

// --------- RDV ----------
$stmt = $pdo->prepare("INSERT INTO rdv (patient_id,staff_id,service_id,date_rdv,heure_debut,heure_fin,duree,statut) VALUES (?,?,?,?,?,?,?,?)");
$patients = [6, 7, 8, 9, 10];
$duree = [30, 30, 45];
for ($i = 0; $i < 15; $i++) {
    $staff = $medecins[array_rand($medecins)];
    $patient = $patients[array_rand($patients)];
    $service = $serviceIds[array_rand($serviceIds)];
    $date = (new DateTime("+" . rand(1, 90) . " days"))->format("Y-m-d");
    $h = rand(9, 16);
    $start = sprintf("%02d:00:00", $h);
    $end = sprintf("%02d:30:00", $h);
    $d = $duree[array_rand($duree)];
    $stmt->execute([$patient, $staff, $service, $date, $start, $end, $d, "PROGRAMME"]);
}

// --------- NEWS ----------
$stmt = $pdo->prepare("INSERT INTO news (titre,contenu,created_by) VALUES (?,?,?)");
for ($i = 1; $i <= 20; $i++) {
    $auteur = $medecins[array_rand($medecins)];
    $stmt->execute(["News #$i", "Contenu automatique de la news numéro $i.", $auteur]);
}

// --------- AUDIT LOG ----------
$stmt = $pdo->prepare("INSERT INTO audit_log (table_name,entity_id,action,user_id,ip_address) VALUES (?,?,?,?,?)");
$stmt->execute(["users", 1, "Création admin", 1, "127.0.0.1"]);
$stmt->execute(["services", 1, "Création service Soins dentaires", 1, "127.0.0.1"]);

echo "✅ Seed terminé avec succès !\n";
