<?php
// Variables attendues avant l'inclusion :
// $targetType  => 'service' ou 'staff'
// $actionUrl   => URL du formulaire (ex: index.php?page=dispo_service_store)
// $targetId    => ID du service ou du staff
// $modalId     => identifiant unique du modal (ex: addDispoServiceModal, editDispoStaffModal12)
// $mode        => 'add' ou 'edit'
// $dispo       => objet DisponibiliteService/Staff (seulement pour edit)
?>

<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= $actionUrl ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="<?= $targetType ?>_id" value="<?= htmlspecialchars($targetId) ?>">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <?= $mode === 'add' ? 'Ajouter une disponibilité' : 'Modifier la disponibilité' ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jour de la semaine</label>
                        <select name="jour_semaine" class="form-select" required>
                            <?php foreach (['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE'] as $jour): ?>
                                <option value="<?= $jour ?>"
                                    <?= ($mode === 'edit' && $dispo->getJourSemaine() === $jour) ? 'selected' : '' ?>>
                                    <?= ucfirst(strtolower($jour)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Heure de début</label>
                        <input type="time" name="start_time" class="form-control"
                            value="<?= $mode === 'edit' ? $dispo->getStartTime()->format('H:i') : '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Heure de fin</label>
                        <input type="time" name="end_time" class="form-control"
                            value="<?= $mode === 'edit' ? $dispo->getEndTime()->format('H:i') : '' ?>" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn <?= $mode === 'add' ? 'btn-success' : 'btn-primary' ?>">
                        <?= $mode === 'add' ? 'Enregistrer' : 'Mettre à jour' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>