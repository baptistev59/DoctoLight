<?php include __DIR__ . '/../layouts/header.php'; ?>

<h2>Créer une nouvelle News</h2>

<?php if (isset($_GET['error']) && $_GET['error'] === 'validation'): ?>
    <p style="color: red;">Le titre et le contenu doivent contenir au moins 3 caractères.</p>
<?php endif; ?>

<form action="index.php?page=create-news-valid" method="post">
    <div>
        <!-- Insertion du token de sécurité -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <label for="titre">Titre :</label>
        <input type="text" id="titre" name="titre" required>
    </div>

    <div>
        <label for="contenu">Contenu :</label>
        <textarea id="contenu" name="contenu" rows="5" required></textarea>
    </div>

    <button type="submit">Créer la news</button>
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>