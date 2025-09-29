<?php include __DIR__ . '/../layouts/header.php'; ?>

<h1>Liste des rendez-vous</h1>

<?php if (!empty($_SESSION['success'])): ?>
    <div style="color: green;">
        <?= htmlspecialchars($_SESSION['success']);
        unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div style="color: red;">
        <?= htmlspecialchars($_SESSION['error']);
        unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Médecin</th>
            <th>Service</th>
            <th>Date</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($rdvs)): ?>
            <?php foreach ($rdvs as $rdv): ?>
                <tr>
                    <td><?= $rdv->getId(); ?></td>
                    <td><?= htmlspecialchars($rdv->getPatientNom() . ' ' . $rdv->getPatientPrenom()); ?></td>
                    <td><?= htmlspecialchars($rdv->getStaffNom() . ' ' . $rdv->getStaffPrenom()); ?></td>
                    <td><?= htmlspecialchars($rdv->getServiceNom()); ?></td>
                    <td><?= (new DateTime($rdv->getDateRdv()))->format('d/m/Y H:i'); ?></td>
                    <td><?= htmlspecialchars($rdv->getStatut()); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">Aucun rendez-vous trouvé</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../layouts/footer.php'; ?>