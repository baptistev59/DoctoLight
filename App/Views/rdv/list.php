<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Planning hebdomadaire</h1>

<form method="get" action="index.php" style="margin-bottom:10px;">
    <input type="hidden" name="page" value="rdv">

    <label for="staff_id">Médecin :</label>
    <select name="staff_id" id="staff_id">
        <option value="">-- Tous --</option>
        <?php foreach ($staffs as $st): ?>
            <option value="<?= $st->getId() ?>" <?= ($selectedStaffId ?? '') == $st->getId() ? 'selected' : '' ?>>
                <?= htmlspecialchars($st->getDisplayName()) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="service_id">Service :</label>
    <select name="service_id" id="service_id">
        <option value="">-- Tous --</option>
        <?php foreach ($services as $s): ?>
            <option value="<?= $s->getId() ?>" <?= ($selectedServiceId ?? '') == $s->getId() ? 'selected' : '' ?>>
                <?= htmlspecialchars($s->getNom()) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="patient_id">Patient :</label>
    <select name="patient_id" id="patient_id">
        <option value="">-- Tous --</option>
        <?php foreach ($patients as $p): ?>
            <option value="<?= $p->getId() ?>" <?= ($selectedPatientId ?? '') == $p->getId() ? 'selected' : '' ?>>
                <?= htmlspecialchars($p->getNom() . ' ' . $p->getPrenom()) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Filtrer</button>
</form>

<!-- Boutons navigation -->
<div style="margin:10px 0;">
    <a href="index.php?page=rdv&week=<?= ($weekOffset - 1) ?>&staff_id=<?= urlencode((string)($selectedStaffId ?? '')) ?>&service_id=<?= urlencode((string)($selectedServiceId ?? '')) ?>&patient_id=<?= urlencode((string)($selectedPatientId ?? '')) ?>">⏮️ Semaine précédente</a>
    &nbsp;|&nbsp;
    <a href="index.php?page=rdv&week=0&staff_id=<?= urlencode((string)($selectedStaffId ?? '')) ?>&service_id=<?= urlencode((string)($selectedServiceId ?? '')) ?>&patient_id=<?= urlencode((string)($selectedPatientId ?? '')) ?>">📅 Semaine en cours</a>
    &nbsp;|&nbsp;
    <a href="index.php?page=rdv&week=<?= ($weekOffset + 1) ?>&staff_id=<?= urlencode((string)($selectedStaffId ?? '')) ?>&service_id=<?= urlencode((string)($selectedServiceId ?? '')) ?>&patient_id=<?= urlencode((string)($selectedPatientId ?? '')) ?>">Semaine suivante ⏭️</a>
</div>

<!-- Rappel filtres sélectionnés -->
<h2>
    Planning
    <?php if (!empty($selectedStaffId)): ?>
        — Médecin :
        <?= htmlspecialchars($staffs[array_search($selectedStaffId, array_column($staffs, 'id'))]->getDisplayName()) ?>
    <?php endif; ?>
    <?php if (!empty($selectedServiceId)): ?>
        — Service :
        <?= htmlspecialchars($services[array_search($selectedServiceId, array_column($services, 'id'))]->getNom()) ?>
    <?php endif; ?>
    <?php if (!empty($selectedPatientId)): ?>
        — Patient :
        <?= htmlspecialchars($patients[array_search($selectedPatientId, array_column($patients, 'id'))]->getNom() . ' ' . $patients[array_search($selectedPatientId, array_column($patients, 'id'))]->getPrenom()) ?>
    <?php endif; ?>
</h2>

<table border="1" cellpadding="6" cellspacing="0" style="min-width:900px;">
    <thead>
        <tr>
            <th>Heure</th>
            <?php foreach ($datesSemaine as $date): ?>
                <th><?= htmlspecialchars($date->format('D d/m')) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($creneaux as $heure => $jours): ?>
            <tr>
                <td style="font-weight:bold;"><?= htmlspecialchars($heure) ?></td>
                <?php foreach ($datesSemaine as $date):
                    $dayStr = $date->format('Y-m-d');
                    $list = $jours[$dayStr] ?? [];
                ?>
                    <td style="vertical-align:top; min-width:180px;">
                        <?php if (!empty($list)): ?>
                            <?php foreach ($list as $e): ?>
                                <div style="margin:2px 0; padding:4px; border:1px solid #99c; background:#eef; border-radius:4px;">
                                    <div><strong><?= htmlspecialchars($e['service_nom']) ?></strong></div>
                                    <div><?= htmlspecialchars('Patient : ' . $e['patient_nom'] . ' ' . $e['patient_prenom']) ?></div>
                                    <div><?= htmlspecialchars('Médecin : ' . $e['staff_nom'] . ' ' . $e['staff_prenom']) ?></div>
                                    <div><?= substr($e['heure_debut'], 0, 5) ?> - <?= substr($e['heure_fin'], 0, 5) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span style="color:#bbb;">-</span>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../layouts/footer.php'; ?>