<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4 text-primary"><i class="bi bi-calendar3"></i> Mes rendez-vous</h1>

    <!-- Messages flash -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($rdvs)): ?>
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Service</th>
                        <th>Médecin</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rdvs as $rdv): ?>
                        <?php
                        $start = new DateTime($rdv['date_rdv'] . ' ' . $rdv['heure_debut']);
                        $now = new DateTime();
                        $diffHours = ($start->getTimestamp() - $now->getTimestamp()) / 3600;
                        $canEdit = $diffHours >= 72; // modifiable/annulable seulement si >= 72h

                        $statut = $rdv['statut'];
                        $badgeClass = match ($statut) {
                            'PROGRAMME' => 'bg-success',
                            'TERMINE'   => 'bg-info text-dark',
                            'ANNULE'    => 'bg-secondary text-light text-decoration-line-through',
                            default     => 'bg-light text-dark'
                        };
                        ?>
                        <tr>
                            <td><?= (new DateTime($rdv['date_rdv']))->format('d/m/Y') ?></td>
                            <td><?= substr($rdv['heure_debut'], 0, 5) ?> - <?= substr($rdv['heure_fin'], 0, 5) ?></td>
                            <td><?= htmlspecialchars($rdv['service_nom']) ?></td>
                            <td><?= htmlspecialchars($rdv['staff_prenom'] . ' ' . strtoupper($rdv['staff_nom'])) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst(strtolower($statut)) ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($canEdit && $statut === 'PROGRAMME'): ?>
                                    <a href="index.php?page=create_rdv&edit_id=<?= $rdv['id'] ?>"
                                        class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i> Modifier
                                    </a>
                                    <a href="index.php?page=rdv_cancel&id=<?= $rdv['id'] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Êtes-vous sûr de vouloir annuler ce RDV ?');">
                                        <i class="bi bi-x-circle"></i> Annuler
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Non modifiable</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center mt-4">
            <i class="bi bi-info-circle"></i> Aucun rendez-vous trouvé.
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>