<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Modifier un rendez-vous</h1>

<form method="post" action="index.php?page=rdv_store" id="rdvForm">

    <!-- Champ caché pour savoir qu'on est en mode édition -->
    <input type="hidden" name="edit_id" value="<?= $rdv->getId() ?>">

    <!-- Patient -->
    <?php if ($isPatient): ?>
        <input type="hidden" name="patient_id" value="<?= $rdv->getPatientId() ?>">
        <p><em>Vous modifiez votre RDV</em></p>
    <?php else: ?>
        <label for="patient_id">Patient :</label>
        <select name="patient_id" id="patient_id">
            <?php foreach ($patients as $p): ?>
                <option value="<?= $p->getId() ?>"
                    <?= $p->getId() == $rdv->getPatientId() ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p->getNom() . ' ' . $p->getPrenom()) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <!-- Service -->
    <label for="service_id">Service :</label>
    <select name="service_id" id="service_id">
        <?php foreach ($services as $s): ?>
            <option value="<?= $s->getId() ?>"
                <?= $s->getId() == $rdv->getServiceId() ? 'selected' : '' ?>>
                <?= htmlspecialchars($s->getNom()) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Médecin -->
    <label for="staff_id">Médecin :</label>
    <select name="staff_id" id="staff_id">
        <?php foreach ($staffs as $st): ?>
            <option value="<?= $st->getId() ?>"
                <?= $st->getId() == $rdv->getStaffId() ? 'selected' : '' ?>>
                <?= htmlspecialchars($st->getDisplayName()) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Date -->
    <label for="date_rdv">Date :</label>
    <input type="date" name="date_rdv" id="date_rdv"
        value="<?= htmlspecialchars($rdv->getDateRdv()->format('Y-m-d')) ?>">

    <!-- Heure début -->
    <label for="heure_rdv">Heure :</label>
    <input type="time" name="heure_rdv" id="heure_rdv"
        value="<?= htmlspecialchars(substr($rdv->getHeureDebut(), 0, 5)) ?>">

    <div style="margin-top:15px;">
        <button type="submit">💾 Enregistrer les modifications</button>
        <a href="index.php?page=rdv_listpatient" style="margin-left:10px;">Annuler</a>
    </div>
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>