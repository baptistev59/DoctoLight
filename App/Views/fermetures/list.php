<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <h1 class="mb-4 text-primary">
        <i class="bi bi-door-closed"></i> Fermetures exceptionnelles du cabinet
    </h1>

    <!-- Messages flash -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']);
                                            unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']);
                                        unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Formulaire d'ajout -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-plus-circle"></i> Ajouter une fermeture
        </div>
        <div class="card-body">
            <form action="index.php?page=fermeture_store" method="post" class="row g-3">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="col-md-4">
                    <label for="date_debut" class="form-label">Date de début :</label>
                    <input type="date" name="date_debut" id="date_debut" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label for="date_fin" class="form-label">Date de fin :</label>
                    <input type="date" name="date_fin" id="date_fin" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label for="motif" class="form-label">Motif (facultatif) :</label>
                    <input type="text" name="motif" id="motif" class="form-control" placeholder="Congés annuels, jour férié...">
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des fermetures -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <i class="bi bi-calendar-x"></i> Liste des fermetures programmées
        </div>
        <div class="card-body p-0">
            <?php if (!empty($fermetures)): ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Date de début</th>
                                <th>Date de fin</th>
                                <th>Motif</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fermetures as $f): ?>
                                <tr>
                                    <td><?= htmlspecialchars($f['id']); ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($f['date_debut']))); ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($f['date_fin']))); ?></td>
                                    <td><?= htmlspecialchars($f['motif'] ?? '—'); ?></td>
                                    <td class="text-center">
                                        <form method="post" action="index.php?page=fermeture_delete&id=<?= $f['id']; ?>"
                                            onsubmit="return confirm('Supprimer cette fermeture ?');" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-muted p-3 mb-0">Aucune fermeture programmée.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>