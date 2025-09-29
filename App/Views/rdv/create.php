<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2>Prendre un RDV</h2>

<?php if (!empty($_SESSION['error'])): ?>
    <div style="color:red"><?= htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form action="index.php?page=rdv_create" method="post">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

    <?php if (!empty($patients) && $_SESSION['user']->hasRole('SECRETAIRE') || $_SESSION['user']->hasRole('ADMIN')): ?>
        <label for="patient_id">Patient :</label>
        <select name="patient_id" required>
            <?php foreach ($patients as $p): ?>
                <option value="<?= $p->getId(); ?>" <?= ($_POST['patient_id'] ?? '') == $p->getId() ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p->getNom() . ' ' . $p->getPrenom()); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <label for="service_id">Service :</label>
    <select name="service_id" id="service_id" required onchange="this.form.submit()">
        <option value="">-- Choisir un service --</option>
        <?php foreach ($services as $s): ?>
            <option value="<?= $s->getId(); ?>" <?= ($selectedServiceId ?? '') == $s->getId() ? 'selected' : '' ?>>
                <?= htmlspecialchars($s->getNom()); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="staff_id">Médecin :</label>
    <select name="staff_id" id="staff_id" required onchange="this.form.submit()">
        <option value="">-- Choisir un médecin --</option>
        <?php foreach ($staffs as $m): ?>
            <option value="<?= $m->getId(); ?>" <?= ($selectedStaffId ?? '') == $m->getId() ? 'selected' : '' ?>>
                <?= htmlspecialchars($m->getNom() . ' ' . $m->getPrenom()); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="date_rdv">Date :</label>
    <input type="date" name="date_rdv" id="date_rdv" value="<?= htmlspecialchars($selectedDate ?? '') ?>" required onchange="this.form.submit()">
</form>

<?php if (!empty($availableSlots)): ?>
    <form action="index.php?page=rdv_store" method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="service_id" value="<?= htmlspecialchars($selectedServiceId); ?>">
        <input type="hidden" name="staff_id" value="<?= htmlspecialchars($selectedStaffId); ?>">
        <input type="hidden" name="date_rdv" value="<?= htmlspecialchars($selectedDate); ?>">
        <?php if (!empty($patients)): ?>
            <input type="hidden" name="patient_id" value="<?= htmlspecialchars($_POST['patient_id']); ?>">
        <?php endif; ?>

        <label for="heure_rdv">Choisir un créneau :</label>
        <select name="heure_rdv" id="heure_rdv" required>
            <?php foreach ($availableSlots as $slot):
                $start = $slot->format('H:i');
                $end   = (clone $slot)->modify("+{$slot->duree} minutes")->format('H:i');
            ?>
                <option value="<?= $start ?>"><?= $start . ' - ' . $end ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Réserver</button>
    </form>
<?php elseif (!empty($selectedServiceId) && !empty($selectedStaffId) && !empty($selectedDate)): ?>
    <p style="color:orange;">Aucun créneau disponible pour ce jour et ce médecin.</p>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>