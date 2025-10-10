<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4 text-primary">
        <i class="bi bi-calendar-week"></i> Planning hebdomadaire
    </h1>

    <!-- Formulaire de filtres -->
    <form method="get" action="index.php" class="card p-3 shadow-sm mb-4">
        <input type="hidden" name="page" value="rdv">

        <div class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-3">
                <?php if ($currentUser->hasRole('MEDECIN')): ?>
                    <input type="hidden" name="staff_id" value="<?= htmlspecialchars($selectedStaffId) ?>">
                <?php else: ?>
                    <label for="staff_id" class="form-label">Médecin :</label>
                    <select name="staff_id" id="staff_id" class="form-select">
                        <option value="">-- Tous les médecins --</option>
                        <?php foreach ($staffs as $st): ?>
                            <option value="<?= $st->getId() ?>" <?= ($selectedStaffId ?? '') == $st->getId() ? 'selected' : '' ?>>
                                <?= htmlspecialchars($st->getDisplayName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="col-md-4 col-lg-3">
                <label for="service_id" class="form-label">Service :</label>
                <select name="service_id" id="service_id" class="form-select">
                    <option value="">-- Tous les services --</option>
                    <?php foreach ($services as $s): ?>
                        <option value="<?= $s->getId() ?>" <?= ($selectedServiceId ?? '') == $s->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s->getNom()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 col-lg-3">
                <label for="patient_id" class="form-label">Patient :</label>
                <select name="patient_id" id="patient_id" class="form-select">
                    <option value="">-- Tous les patients --</option>
                    <?php foreach ($patients as $p): ?>
                        <option value="<?= $p->getId() ?>" <?= ($selectedPatientId ?? '') == $p->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p->getNom() . ' ' . $p->getPrenom()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-12 col-lg-3 text-md-end">
                <button type="submit" class="btn btn-primary mt-2">
                    <i class="bi bi-filter"></i> Filtrer
                </button>
            </div>
        </div>
    </form>

    <!-- Navigation semaines -->
    <div class="d-flex justify-content-center gap-2 mb-4">
        <a class="btn btn-outline-secondary"
            href="index.php?page=rdv&week=<?= ($weekOffset - 1) ?>&staff_id=<?= urlencode((string)($selectedStaffId ?? '')) ?>&service_id=<?= urlencode((string)($selectedServiceId ?? '')) ?>&patient_id=<?= urlencode((string)($selectedPatientId ?? '')) ?>">
            ⏮️ Semaine précédente
        </a>
        <a class="btn btn-outline-primary"
            href="index.php?page=rdv&week=0&staff_id=<?= urlencode((string)($selectedStaffId ?? '')) ?>&service_id=<?= urlencode((string)($selectedServiceId ?? '')) ?>&patient_id=<?= urlencode((string)($selectedPatientId ?? '')) ?>">
            📅 Semaine en cours
        </a>
        <a class="btn btn-outline-secondary"
            href="index.php?page=rdv&week=<?= ($weekOffset + 1) ?>&staff_id=<?= urlencode((string)($selectedStaffId ?? '')) ?>&service_id=<?= urlencode((string)($selectedServiceId ?? '')) ?>&patient_id=<?= urlencode((string)($selectedPatientId ?? '')) ?>">
            Semaine suivante ⏭️
        </a>
    </div>

    <!-- Rappel des filtres sélectionnés -->
    <h4 class="text-secondary mb-3">
        <i class="bi bi-calendar-range"></i> Planning
        <?php if (!empty($selectedStaffName)): ?>
            — <span class="fw-semibold text-dark">Médecin :</span>
            <?= htmlspecialchars($selectedStaffName); ?>
        <?php endif; ?>
        <?php if (!empty($selectedServiceId)): ?>
            — <span class="fw-semibold text-dark">Service :</span>
            <?= htmlspecialchars($services[array_search($selectedServiceId, array_column($services, 'id'))]->getNom()) ?>
        <?php endif; ?>
        <?php if (!empty($selectedPatientId)): ?>
            — <span class="fw-semibold text-dark">Patient :</span>
            <?= htmlspecialchars($patients[array_search($selectedPatientId, array_column($patients, 'id'))]->getNom() . ' ' . $patients[array_search($selectedPatientId, array_column($patients, 'id'))]->getPrenom()) ?>
        <?php endif; ?>
    </h4>

    <!-- Tableau planning -->
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered align-middle text-center">
            <thead class="table-primary">
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
                        <td class="fw-bold"><?= htmlspecialchars($heure) ?></td>
                        <?php foreach ($datesSemaine as $date):
                            $dayStr = $date->format('Y-m-d');
                            $list = $jours[$dayStr] ?? [];
                        ?>
                            <td style="min-width: 200px;">
                                <?php if (!empty($list)): ?>
                                    <?php foreach ($list as $e): ?>
                                        <?php
                                        $statut = $e['statut'] ?? 'PROGRAMME';
                                        $bgClass = match ($statut) {
                                            'PROGRAMME' => 'bg-success-subtle border-success',
                                            'TERMINE'   => 'bg-info-subtle border-info',
                                            'ANNULE'    => 'bg-light text-muted border-secondary text-decoration-line-through',
                                            default     => 'bg-light'
                                        };
                                        ?>
                                        <div class="border rounded p-2 mb-2 text-start <?= $bgClass ?>">
                                            <div class="fw-bold text-primary">
                                                <?= htmlspecialchars($e['service_nom']) ?>
                                            </div>
                                            <div><i class="bi bi-person"></i>
                                                <?= htmlspecialchars($e['patient_nom'] . ' ' . $e['patient_prenom']) ?>
                                            </div>
                                            <div><i class="bi bi-stethoscope"></i>
                                                <?= htmlspecialchars($e['staff_nom'] . ' ' . $e['staff_prenom']) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?= substr($e['heure_debut'], 0, 5) ?> - <?= substr($e['heure_fin'], 0, 5) ?>
                                            </div>
                                            <div class="mt-1">
                                                <?php if ($statut === 'PROGRAMME'): ?>
                                                    <a href="index.php?page=create_rdv&edit_id=<?= $e['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="bi bi-pencil"></i> Modifier
                                                    </a>
                                                    <a href="index.php?page=rdv_cancel&id=<?= $e['id'] ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Voulez-vous annuler ce RDV ?');">
                                                        <i class="bi bi-x-circle"></i> Annuler
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic">Aucune action</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
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
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>