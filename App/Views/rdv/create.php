<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Créer un rendez-vous</h1>

<form method="post" action="index.php?page=rdv_store" id="rdvForm">

    <?php if (!empty($patients)): ?>
        <label for="patient_filter">Filtrer patient :</label>
        <input type="text" id="patient_filter" placeholder="Nom ou prénom">

        <label for="patient_id">Patient :</label>
        <select name="patient_id" id="patient_id">
            <?php
            usort($patients, fn($a, $b) => strcmp($a->getNom() . ' ' . $a->getPrenom(), $b->getNom() . ' ' . $b->getPrenom()));
            foreach ($patients as $p): ?>
                <option value="<?= $p->getId() ?>" <?= ($selectedPatientId ?? '') == $p->getId() ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p->getNom() . ' ' . $p->getPrenom()) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <label for="service_id">Service :</label>
    <select name="service_id" id="service_id">
        <?php foreach ($services as $s): ?>
            <option value="<?= $s->getId() ?>" <?= ($selectedServiceId ?? '') == $s->getId() ? 'selected' : '' ?>>
                <?= htmlspecialchars($s->getNom()) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="staff_id">Médecin :</label>
    <select name="staff_id" id="staff_id">
        <?php foreach ($staffs as $st): ?>
            <option value="<?= $st->getId() ?>" <?= ($selectedStaffId ?? '') == $st->getId() ? 'selected' : '' ?>>
                <?= htmlspecialchars($st->getDisplayName()) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div style="margin: 10px 0;">
        <button type="button" onclick="viewPlanning()">Voir le planning complet</button>
    </div>
    <div>
        <button type="button" onclick="changeWeek(-1)">Semaine précédente</button>
        <button type="button" onclick="changeWeek(1)">Semaine suivante</button>
    </div>

    <h2>Créneaux disponibles</h2>

    <table border="1" id="slotsTable" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Heure</th>
                <?php foreach ($datesSemaine as $date): ?>
                    <th><?= htmlspecialchars($date->format('D d/m')) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($availableSlots as $heure => $jours): ?>
                <tr>
                    <td style="font-weight:bold; white-space:nowrap;"><?= $heure ?></td>
                    <?php foreach ($datesSemaine as $date):
                        $dayStr = $date->format('Y-m-d');
                        $slot = $jours[$dayStr] ?? null;
                    ?>
                        <td style="text-align:center; min-width:120px;">
                            <?php if ($slot): ?>
                                <button type="button" class="slotBtn"
                                    data-date="<?= $slot['start']->format('Y-m-d') ?>"
                                    data-time="<?= $slot['start']->format('H:i:s') ?>"
                                    style="background-color: <?= $slot['disponible'] ? '#9f9' : '#f99' ?>;
                                           padding:4px 8px; border-radius:4px;"
                                    <?= $slot['disponible'] ? '' : 'disabled' ?>>
                                    <?= $slot['start']->format('H:i') ?> - <?= $slot['end']->format('H:i') ?>
                                </button>
                            <?php else: ?>
                                <span style="color:#999;">-</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="date_rdv" id="date_rdv">
    <input type="hidden" name="heure_rdv" id="heure_rdv">

    <button type="submit">Créer le RDV</button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filtre patient
        const filterInput = document.getElementById('patient_filter');
        const patientSelect = document.getElementById('patient_id');
        if (filterInput && patientSelect) {
            filterInput.addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                for (let i = 0; i < patientSelect.options.length; i++) {
                    const text = patientSelect.options[i].text.toLowerCase();
                    patientSelect.options[i].style.display = text.includes(filter) ? '' : 'none';
                }
            });
        }

        // Sélection créneau
        const slotBtns = document.querySelectorAll('.slotBtn');
        slotBtns.forEach(btn => {
            if (!btn.disabled) { // on bloque les non dispo
                btn.addEventListener('click', function() {
                    document.getElementById('date_rdv').value = this.dataset.date;
                    document.getElementById('heure_rdv').value = this.dataset.time;

                    slotBtns.forEach(b => b.style.border = '');
                    this.style.border = '2px solid blue';
                });
            }
        });
    });

    // Navigation semaine
    function changeWeek(offset) {
        const url = new URL(window.location.href);
        let week = parseInt(url.searchParams.get('week') || 0);
        week += offset;

        const serviceId = document.getElementById('service_id')?.value || '';
        const staffId = document.getElementById('staff_id')?.value || '';
        const patientId = document.getElementById('patient_id')?.value || '';

        url.searchParams.set('week', week);
        if (serviceId) url.searchParams.set('service_id', serviceId);
        if (staffId) url.searchParams.set('staff_id', staffId);
        if (patientId) url.searchParams.set('patient_id', patientId);

        window.location.href = url.toString();
    }

    function viewPlanning() {
        const url = new URL(window.location.href);
        const serviceId = document.getElementById('service_id')?.value || '';
        const staffId = document.getElementById('staff_id')?.value || '';
        const patientId = document.getElementById('patient_id')?.value || '';

        if (serviceId) url.searchParams.set('service_id', serviceId);
        if (staffId) url.searchParams.set('staff_id', staffId);
        if (patientId) url.searchParams.set('patient_id', patientId);
        url.searchParams.set('view', 'planning');

        window.location.href = url.toString();
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>