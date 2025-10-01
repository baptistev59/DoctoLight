<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Créer un rendez-vous</h1>

<style>
    .slotBtn {
        padding: 6px 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        cursor: pointer;
        min-width: 120px;
        transition: background .15s ease, border-color .15s ease;
    }

    .slotBtn:disabled {
        cursor: not-allowed;
        opacity: .6;
    }

    .slot--dispo {
        background: #9f9;
    }

    /* vert */
    .slot--indispo {
        background: #f99;
    }

    /* rouge */
    .slot--selected {
        background: #2196f3;
        color: #fff;
        border-color: #1976d2;
    }

    #rdvResume {
        margin-top: 15px;
        padding: 10px;
        border: 1px solid #ccc;
        background: #f5f5f5;
        border-radius: 6px;
        display: none;
    }

    #rdvResume strong {
        color: #1976d2;
    }
</style>

<form method="post" action="index.php?page=rdv_store" id="rdvForm">

    <!-- Patient -->
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

    <!-- Service -->
    <label for="service_id">Service :</label>
    <select name="service_id" id="service_id">
        <?php foreach ($services as $s): ?>
            <option value="<?= $s->getId() ?>" <?= ($selectedServiceId ?? '') == $s->getId() ? 'selected' : '' ?>>
                <?= htmlspecialchars($s->getNom()) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Médecin -->
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

    <!-- Navigation semaine -->
    <div style="margin: 10px 0;">
        <button type="button" onclick="changeWeek(-1)">⏮️ Semaine précédente</button>
        <button type="button" onclick="goCurrentWeek()">📅 Semaine en cours</button>
        <button type="button" onclick="changeWeek(1)">Semaine suivante ⏭️</button>
    </div>

    <!-- Rappel médecin + service -->
    <h2>
        Créneaux disponibles
        <?php if (!empty($selectedStaffId) && !empty($selectedServiceId)): ?>
            — <?= htmlspecialchars($staffs[array_search($selectedStaffId, array_column($staffs, 'id'))]->getDisplayName()) ?>
            (<?= htmlspecialchars($services[array_search($selectedServiceId, array_column($services, 'id'))]->getNom()) ?>)
        <?php endif; ?>
    </h2>

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
                        <td style="text-align:center;">
                            <?php if ($slot): ?>
                                <?php
                                $isFree = !empty($slot['disponible']);
                                $btnClasses = 'slotBtn ' . ($isFree ? 'slot--dispo' : 'slot--indispo');
                                ?>
                                <button type="button"
                                    class="<?= $btnClasses ?>"
                                    data-date="<?= $slot['start']->format('Y-m-d') ?>"
                                    data-start="<?= $slot['start']->format('H:i:s') ?>"
                                    data-end="<?= $slot['end']->format('H:i:s') ?>"
                                    <?= $isFree ? '' : 'disabled' ?>>
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

    <!-- Champs cachés -->
    <input type="hidden" name="date_rdv" id="date_rdv">
    <input type="hidden" name="heure_rdv" id="heure_rdv">
    <input type="hidden" name="heure_fin_selected" id="heure_fin_selected">

    <!-- Résumé -->
    <div id="rdvResume">
        <p><strong>Créneau choisi :</strong> <span id="resumeText">Aucun</span></p>
        <p><strong>Patient :</strong> <span id="resumePatient"></span></p>
        <p><strong>Service :</strong> <span id="resumeService"></span></p>
        <p><strong>Médecin :</strong> <span id="resumeStaff"></span></p>
    </div>

    <div style="margin-top:10px;">
        <button type="submit" id="submitBtn">Créer le RDV</button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateField = document.getElementById('date_rdv');
        const startField = document.getElementById('heure_rdv');
        const endField = document.getElementById('heure_fin_selected');

        const resumeDiv = document.getElementById('rdvResume');
        const resumeText = document.getElementById('resumeText');
        const resumePatient = document.getElementById('resumePatient');
        const resumeService = document.getElementById('resumeService');
        const resumeStaff = document.getElementById('resumeStaff');

        const slotBtns = document.querySelectorAll('.slotBtn.slot--dispo');
        slotBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.slotBtn.slot--selected').forEach(b => b.classList.remove('slot--selected'));
                this.classList.add('slot--selected');

                dateField.value = this.dataset.date;
                startField.value = this.dataset.start;
                endField.value = this.dataset.end;

                const patientSelect = document.getElementById('patient_id');
                const serviceSelect = document.getElementById('service_id');
                const staffSelect = document.getElementById('staff_id');

                resumeDiv.style.display = 'block';
                resumeText.textContent = `${this.dataset.date} de ${this.dataset.start.substring(0,5)} à ${this.dataset.end.substring(0,5)}`;
                resumePatient.textContent = patientSelect ? patientSelect.options[patientSelect.selectedIndex].text : '';
                resumeService.textContent = serviceSelect ? serviceSelect.options[serviceSelect.selectedIndex].text : '';
                resumeStaff.textContent = staffSelect ? staffSelect.options[staffSelect.selectedIndex].text : '';
            });
        });

        // filtre patient
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
    });

    // navigation semaine
    function changeWeek(offset) {
        const url = new URL(window.location.href);
        let week = parseInt(url.searchParams.get('week') || 0);
        week += offset;

        applyFilters(url);
        url.searchParams.set('week', week);

        window.location.href = url.toString();
    }

    function goCurrentWeek() {
        const url = new URL(window.location.href);
        applyFilters(url);
        url.searchParams.set('week', 0);
        window.location.href = url.toString();
    }

    function viewPlanning() {
        const url = new URL(window.location.href);
        applyFilters(url);
        url.searchParams.set('view', 'planning');
        window.location.href = url.toString();
    }

    function applyFilters(url) {
        const serviceId = document.getElementById('service_id')?.value || '';
        const staffId = document.getElementById('staff_id')?.value || '';
        const patientId = document.getElementById('patient_id')?.value || '';
        if (serviceId) url.searchParams.set('service_id', serviceId);
        if (staffId) url.searchParams.set('staff_id', staffId);
        if (patientId) url.searchParams.set('patient_id', patientId);
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>