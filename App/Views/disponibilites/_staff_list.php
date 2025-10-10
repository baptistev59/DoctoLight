<div class="card shadow-sm p-4 mt-5">
    <h4 class="text-primary mb-4">
        <i class="bi bi-clock-history"></i> Disponibilités du médecin
    </h4>

    <!-- Bouton d’ajout -->
    <?php if (
        $currentUser && ($currentUser->hasRole(['ADMIN', 'SECRETAIRE'])
            || ($currentUser->hasRole('MEDECIN') && $currentUser->getId() === $userToView->getId()))
    ): ?>
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addStaffDispoModal">
            <i class="bi bi-plus-circle"></i> Ajouter une disponibilité
        </button>

        <?php
        // Inclut le modal d’ajout (réutilise ton fichier générique)
        $targetType = 'user';
        $actionUrl = 'index.php?page=dispo_staff_store';
        $targetId = $userToView->getId();
        $modalId = 'addStaffDispoModal';
        $mode = 'add';
        include __DIR__ . '/_modal_dispo.php';
        ?>
    <?php endif; ?>

    <?php if (!empty($dispos)): ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle shadow-sm">
                <thead class="table-light">
                    <tr>
                        <th>Jour</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <?php if (
                            $currentUser && ($currentUser->hasRole(['ADMIN', 'SECRETAIRE'])
                                || ($currentUser->hasRole('MEDECIN') && $currentUser->getId() === $userToView->getId()))
                        ): ?>
                            <th class="text-center">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dispos as $dispo): ?>
                        <tr>
                            <td><?= htmlspecialchars($dispo->getJourSemaine()); ?></td>
                            <td><?= htmlspecialchars($dispo->getStartTime()->format('H:i')); ?></td>
                            <td><?= htmlspecialchars($dispo->getEndTime()->format('H:i')); ?></td>

                            <?php if (
                                $currentUser && ($currentUser->hasRole(['ADMIN', 'SECRETAIRE'])
                                    || ($currentUser->hasRole('MEDECIN') && $currentUser->getId() === $userToView->getId()))
                            ): ?>
                                <td class="text-center">
                                    <!-- Bouton d’édition -->
                                    <button class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editStaffDispoModal<?= $dispo->getId(); ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <!-- Suppression -->
                                    <form action="index.php?page=dispo_staff_delete&id=<?= $dispo->getId(); ?>"
                                        method="post"
                                        class="d-inline"
                                        onsubmit="return confirm('Supprimer cette disponibilité ?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>

                                <?php
                                // Inclut le modal d’édition (générique)
                                $targetType = 'user';
                                $actionUrl = 'index.php?page=dispo_staff_update&id=' . $dispo->getId();
                                $targetId = $userToView->getId();
                                $modalId = 'editStaffDispoModal' . $dispo->getId();
                                $mode = 'edit';
                                include __DIR__ . '/_modal_dispo.php';
                                ?>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info mb-0">
            Aucune disponibilité enregistrée pour ce médecin.
        </div>
    <?php endif; ?>
</div>