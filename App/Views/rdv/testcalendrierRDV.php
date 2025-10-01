<?php
// --- Paramètres de base ---
$jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
$heures = range(8, 18); // Créneaux de 8h à 18h

// Exemple de disponibilités récupérées depuis la BDD
// Ici on simule, mais à terme ça viendra de disponibilite_staff + disponibilite_service
$dispos = [
    "Lundi" => [9, 10, 14, 15],
    "Mardi" => [10, 11, 16],
    "Jeudi" => [8, 9, 17],
    "Vendredi" => [13, 14, 15],
];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Calendrier hebdomadaire</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            text-align: center;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        th {
            background: #f0f0f0;
        }

        .dispo {
            background: #c8f7c5;
            cursor: pointer;
        }

        .indispo {
            background: #f7c5c5;
            color: #666;
        }
    </style>
</head>

<body>

    <h2>Calendrier hebdomadaire</h2>
    <table>
        <tr>
            <th>Heure</th>
            <?php foreach ($jours as $jour): ?>
                <th><?= $jour ?></th>
            <?php endforeach; ?>
        </tr>

        <?php foreach ($heures as $h): ?>
            <tr>
                <td><?= $h ?>h</td>
                <?php foreach ($jours as $jour): ?>
                    <?php if (!empty($dispos[$jour]) && in_array($h, $dispos[$jour])): ?>
                        <td class="dispo">
                            <form method="post" action="reserver.php" style="margin:0;">
                                <input type="hidden" name="jour" value="<?= $jour ?>">
                                <input type="hidden" name="heure" value="<?= $h ?>">
                                <button type="submit"><?= $h ?>h</button>
                            </form>
                        </td>
                    <?php else: ?>
                        <td class="indispo">X</td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>

</body>

</html>