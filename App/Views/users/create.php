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
                <option value="<?= $p->getId() ?>"><?= htmlspecialchars($p->getNom() . ' ' . $p->getPrenom()) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <label for="service_id">Service :</label>
    <select name="service_id" id="service_id">
        <?php foreach ($services as $s): ?>
            <option value="<?= $s->getId() ?>" <?= ($selectedServiceId ?? '') == $s->getId() ? 'selected' : '' ?>><?= htmlspecialchars($s->getName()) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="staff_id">Médecin :</label>
    <select name="staff_id" id="staff_id">
        <?php foreach ($staffs as $st): ?>
            <option value="<?= $st->getId() ?>" <?= ($selectedStaffId ?? '') == $st->getId() ? 'selected' : '' ?>><?= htmlspecialchars($st->getDisplayName()) ?></option>
        <?php endforeach; ?>
    </select>

    <div>
        <button type="button" onclick="changeWeek(-1)">Semaine précédente</button>
        <button type="button" onclick="changeWeek(1)">Semaine suivante</button>
    </div>

    <h2>Créneaux disponibles</h2>
    <table border="1" id="slotsTable">
        <thead>
            <tr>
                <th>Jour</th>
                <th>Créneaux</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($datesSemaine as $date):
                $dayStr = $date->format('Y-m-d');
                $daySlots = array_filter($availableSlots ?? [], fn($slot) => $slot['start']->format('Y-m-d') === $dayStr);
            ?>
                <tr>
                    <td><?= $date->format('l d/m') ?></td>
                    <td>
                        <?php if ($daySlots): ?>
                            <?php foreach ($daySlots as $slot): ?>
                                <button type="button" class="slotBtn"
                                    data-date="<?= $slot['start']->format('Y-m-d') ?>"
                                    data-time="<?= $slot['start']->format('H:i:s') ?>">
                                    <?= $slot['start']->format('H:i') ?> - <?= $slot['end']->format('H:i') ?>
                                </button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            Aucun créneau
                        <?php endif; ?>
                    </td>
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
        filterInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            for (let i = 0; i < patientSelect.options.length; i++) {
                const text = patientSelect.options[i].text.toLowerCase();
                patientSelect.options[i].style.display = text.includes(filter) ? '' : 'none';
            }
        });

        // Sélection créneau
        const slotBtns = document.querySelectorAll('.slotBtn');
        slotBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('date_rdv').value = this.dataset.date;
                document.getElementById('heure_rdv').value = this.dataset.time;

                // Marquer visuellement
                slotBtns.forEach(b => b.style.backgroundColor = '');
                this.style.backgroundColor = '#9f9';
            });
        });
    });

    // Navigation semaine
    function changeWeek(offset) {
        const url = new URL(window.location.href);
        let week = parseInt(url.searchParams.get('week') || 0);
        week += offset;
        url.searchParams.set('week', week);
        window.location.href = url.toString();
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>