<div class="card shadow-sm p-4 mt-5">
  <h4 class="text-primary mb-4">
    <i class="bi bi-clock"></i> Disponibilités du service
  </h4>

  <!-- Bouton d’ajout -->
  <?php if ($currentUser && $currentUser->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addServiceDispoModal">
      <i class="bi bi-plus-circle"></i> Ajouter une disponibilité
    </button>
  <?php endif; ?>

  <?php if (!empty($dispos)): ?>
    <div class="table-responsive">
      <table class="table table-striped align-middle shadow-sm">
        <thead class="table-light">
          <tr>
            <th>Jour</th>
            <th>Heure de début</th>
            <th>Heure de fin</th>
            <?php if ($currentUser && $currentUser->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
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

              <?php if ($currentUser && $currentUser->hasRole(['ADMIN', 'SECRETAIRE'])): ?>
                <td class="text-center">
                  <!-- Bouton de modification -->
                  <button class="btn btn-sm btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#editServiceDispoModal<?= $dispo->getId(); ?>">
                    <i class="bi bi-pencil-square"></i>
                  </button>

                  <!-- Suppression -->
                  <form action="index.php?page=dispo_service_delete&id=<?= $dispo->getId(); ?>"
                    method="post" class="d-inline"
                    onsubmit="return confirm('Supprimer cette disponibilité ?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              <?php endif; ?>
            </tr>

            <!-- Modale de modification -->
            <div class="modal fade" id="editServiceDispoModal<?= $dispo->getId(); ?>" tabindex="-1" aria-labelledby="editServiceDispoModalLabel<?= $dispo->getId(); ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="index.php?page=dispo_service_update" method="post">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editServiceDispoModalLabel<?= $dispo->getId(); ?>">Modifier la disponibilité</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                      <input type="hidden" name="id" value="<?= $dispo->getId(); ?>">
                      <input type="hidden" name="service_id" value="<?= $service->getId(); ?>">

                      <div class="mb-3">
                        <label for="jour_semaine<?= $dispo->getId(); ?>" class="form-label">Jour</label>
                        <select name="jour_semaine" id="jour_semaine<?= $dispo->getId(); ?>" class="form-select" required>
                          <?php
                          $jours = ['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE'];
                          foreach ($jours as $jour):
                            $selected = ($jour === $dispo->getJourSemaine()) ? 'selected' : '';
                            echo "<option value='$jour' $selected>$jour</option>";
                          endforeach;
                          ?>
                        </select>
                      </div>

                      <div class="mb-3">
                        <label for="start<?= $dispo->getId(); ?>" class="form-label">Heure de début</label>
                        <input type="time" class="form-control" id="start<?= $dispo->getId(); ?>" name="start_time"
                          value="<?= $dispo->getStartTime()->format('H:i'); ?>" required>
                      </div>

                      <div class="mb-3">
                        <label for="end<?= $dispo->getId(); ?>" class="form-label">Heure de fin</label>
                        <input type="time" class="form-control" id="end<?= $dispo->getId(); ?>" name="end_time"
                          value="<?= $dispo->getEndTime()->format('H:i'); ?>" required>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                      <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-info mb-0">
      Aucune disponibilité n'est définie pour ce service.
    </div>
  <?php endif; ?>
</div>

<!-- Modale d’ajout -->
<div class="modal fade" id="addServiceDispoModal" tabindex="-1" aria-labelledby="addServiceDispoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="index.php?page=dispo_service_store" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="addServiceDispoModalLabel">Ajouter une disponibilité</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
          <input type="hidden" name="service_id" value="<?= $service->getId(); ?>">

          <div class="mb-3">
            <label for="jour_semaine_add" class="form-label">Jour</label>
            <select name="jour_semaine" id="jour_semaine_add" class="form-select" required>
              <option value="">-- Sélectionner --</option>
              <?php
              foreach (['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE'] as $jour) {
                echo "<option value='$jour'>$jour</option>";
              }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="start_add" class="form-label">Heure de début</label>
            <input type="time" class="form-control" id="start_add" name="start_time" required>
          </div>

          <div class="mb-3">
            <label for="end_add" class="form-label">Heure de fin</label>
            <input type="time" class="form-control" id="end_add" name="end_time" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
</div>