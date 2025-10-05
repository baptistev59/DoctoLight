<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4 text-primary">
        <i class="bi bi-calendar-plus"></i>
        <?= !empty($editId) ? "Modifier un rendez-vous" : "Créer un rendez-vous" ?>
    </h1>

    <!-- Messages flash -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="post" action="index.php?page=rdv_store" id="rdvForm" class="card p-4 shadow-sm">
        <?php
        $isPatient   = $isPatient ?? false;
        $currentUser = $currentUser ?? null;
        ?>

        <!-- Sélection patient -->
        <div class="mb-3">
            <?php if ($isPatient): ?>
                <?php if (!empty($editId)): ?>
                    <input type="hidden" name="edit_id" value="<?= $editId ?>">
                <?php endif; ?>
                <input type="hidden" name="patient_id" id="patient_id" value="<?= (int)$selectedPatientId ?>">
                <p><em>Vous réservez pour :</em>
                    <strong><?= htmlspecialchars($currentUser->getNom() . ' ' . $currentUser->getPrenom()) ?></strong>
                </p>
            <?php elseif (!empty($patients)): ?>
                <label for="patient_id" class="form-label">Patient :</label>
                <input type="text" id="patient_filter" class="form-control mb-2" placeholder="Filtrer par nom ou prénom">
                <select name="patient_id" id="patient_id" class="form-select">
                    <?php
                    usort($patients, fn($a, $b) => strcmp($a->getNom() . ' ' . $a->getPrenom(), $b->getNom() . ' ' . $b->getPrenom()));
                    foreach ($patients as $p): ?>
                        <option value="<?= $p->getId() ?>" <?= ($selectedPatientId ?? '') == $p->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p->getNom() . ' ' . $p->getPrenom()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <!-- Sélection service et médecin -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="service_id" class="form-label">Service :</label>
                <select name="service_id" id="service_id" class="form-select">
                    <?php foreach ($services as $s): ?>
                        <option value="<?= $s->getId() ?>" <?= ($selectedServiceId ?? '') == $s->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s->getNom()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="staff_id" class="form-label">Médecin :</label>
                <select name="staff_id" id="staff_id" class="form-select">
                    <?php foreach ($staffs as $st): ?>
                        <option value="<?= $st->getId() ?>" <?= ($selectedStaffId ?? '') == $st->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($st->getDisplayName()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Boutons de navigation -->
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-outline-primary" onclick="viewPlanning()">
                <i class="bi bi-calendar-week"></i> Voir le planning complet
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="changeWeek(-1)">⏮️ Semaine précédente</button>
            <button type="button" class="btn btn-outline-secondary" onclick="goCurrentWeek()">📅 Semaine en cours</button>
            <button type="button" class="btn btn-outline-secondary" onclick="changeWeek(1)">Semaine suivante ⏭️</button>
        </div>

        <!-- Créneaux -->
        <h4 class="mt-4 mb-3 text-primary">
            Créneaux disponibles
            <?php if (!empty($selectedStaffName) || !empty($selectedServiceName)): ?>
                — <?= htmlspecialchars(trim($selectedStaffName)) ?>
                <?= !empty($selectedStaffName) && !empty($selectedServiceName) ? ' · ' : '' ?>
                <?= htmlspecialchars(trim($selectedServiceName)) ?>
            <?php endif; ?>
        </h4>

        <?php if (empty($selectedStaffId) || empty($selectedServiceId)): ?>
            <div class="alert alert-info">Sélectionnez un <strong>médecin</strong> et un <strong>service</strong> pour voir les créneaux.</div>
        <?php else: ?>
            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
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
                                <td><strong><?= htmlspecialchars($heure) ?></strong></td>
                                <?php foreach ($datesSemaine as $date):
                                    $dayStr = $date->format('Y-m-d');
                                    $slot = $jours[$dayStr] ?? null;
                                    $isEditSlot = !empty($editDate) && !empty($editStart)
                                        && $editDate === $dayStr
                                        && substr($editStart, 0, 5) === substr($heure, 0, 5);
                                ?>
                                    <td>
                                        <?php if ($slot): ?>
                                            <?php
                                            $isFree = !empty($slot['disponible']);
                                            $btnClasses = 'btn btn-sm ' . ($isFree ? 'btn-success' : 'btn-danger');
                                            if ($isEditSlot) $btnClasses .= ' btn-primary active';
                                            ?>
                                            <button type="button"
                                                class="<?= $btnClasses ?> slotBtn"
                                                data-dispo="<?= $isFree ? 1 : 0 ?>"
                                                data-date="<?= $slot['start']->format('Y-m-d') ?>"
                                                data-start="<?= $slot['start']->format('H:i:s') ?>"
                                                data-end="<?= $slot['end']->format('H:i:s') ?>"
                                                <?= $isFree || $isEditSlot ? '' : 'disabled' ?>>
                                                <?= $slot['start']->format('H:i') ?> - <?= $slot['end']->format('H:i') ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Champs cachés -->
        <input type="hidden" name="date_rdv" id="date_rdv" value="<?= $editDate ?? '' ?>">
        <input type="hidden" name="heure_rdv" id="heure_rdv" value="<?= $editStart ?? '' ?>">
        <input type="hidden" name="heure_fin_selected" id="heure_fin_selected" value="<?= $editEnd ?? '' ?>">
        <?php if (!empty($editId)): ?>
            <input type="hidden" name="edit_id" value="<?= $editId ?>">
        <?php endif; ?>

        <!-- Résumé -->
        <div id="rdvResume" class="alert alert-secondary <?= !empty($editId) ? '' : 'd-none' ?>">
            <p><strong>Créneau choisi :</strong>
                <span id="resumeText"><?= !empty($editId) ? "$editDate de " . substr($editStart, 0, 5) . " à " . substr($editEnd, 0, 5) : 'Aucun' ?></span>
            </p>
            <p><strong>Patient :</strong> <span id="resumePatient">
                    <?= htmlspecialchars($selectedPatientName ?? ($currentUser ? $currentUser->getNom() . ' ' . $currentUser->getPrenom() : '')) ?>
                </span></p>
            <p><strong>Service :</strong> <span id="resumeService"><?= htmlspecialchars($selectedServiceName ?? '') ?></span></p>
            <p><strong>Médecin :</strong> <span id="resumeStaff"><?= htmlspecialchars($selectedStaffName ?? '') ?></span></p>
        </div>

        <div class="mt-3">
            <button type="submit" id="submitBtn" class="btn btn-primary" <?= empty($editId) ? 'disabled' : '' ?>>
                <?= !empty($editId) ? "Modifier le rendez-vous" : "Créer le rendez-vous" ?>
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateField = document.getElementById('date_rdv');
        const startField = document.getElementById('heure_rdv');
        const endField = document.getElementById('heure_fin_selected');
        const submitBtn = document.getElementById('submitBtn');
        const resumeDiv = document.getElementById('rdvResume');
        const resumeText = document.getElementById('resumeText');
        const slotBtns = document.querySelectorAll('.slotBtn');

        function updateSubmitState() {
            submitBtn.disabled = !(dateField.value && startField.value);
        }

        slotBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Supprime les classes de sélection sur tous les boutons
                document.querySelectorAll('.slotBtn').forEach(b => {
                    b.classList.remove('btn-primary', 'active');
                    if (b.dataset.dispo === "1") {
                        b.classList.add('btn-success');
                    } else {
                        b.classList.add('btn-danger');
                    }
                });

                // Applique le style bleu sur celui qu’on sélectionne
                this.classList.remove('btn-success', 'btn-danger');
                this.classList.add('btn-primary', 'active');

                dateField.value = this.dataset.date;
                startField.value = this.dataset.start;
                endField.value = this.dataset.end;

                resumeDiv.classList.remove('d-none');
                resumeText.textContent = `${this.dataset.date} de ${this.dataset.start.substring(0,5)} à ${this.dataset.end.substring(0,5)}`;
                updateSubmitState();
            });
        });


        if (dateField.value && startField.value) {
            resumeDiv.classList.remove('d-none');
            updateSubmitState();
        }
    });

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
        const patient = document.getElementById('patient_id')?.value || '';
        if (serviceId) url.searchParams.set('service_id', serviceId);
        if (staffId) url.searchParams.set('staff_id', staffId);
        if (patient) url.searchParams.set('patient_id', patient);
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>