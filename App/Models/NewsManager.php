<?php
class NewsManager
{
    private PDO $pdo;
    private UserManager $userManager;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userManager = new UserManager($pdo, []);
    }

    // Récupérer toutes les news
    public function getAllNews(): array
    {
        $sql = "SELECT * FROM news ORDER BY created_at DESC";

        $request = $this->pdo->prepare($sql);
        $request->execute();

        $newsList = [];
        while ($news = $request->fetch(PDO::FETCH_ASSOC)) {
            $newsList[] = new News($news);
        }
        return $newsList;
    }

    // Récupérer un certains nombre de news. Limite à 5 news si aucun paramètre n'est donné
    public function getLatest($limit = 5)
    {
        $limit = (int)$limit; // cast obligatoire en entier pour la sécurité (injection SQL)
        $sql = "SELECT * FROM news ORDER BY created_at DESC LIMIT $limit";
        $request = $this->pdo->prepare($sql);
        $request->execute();

        $newsList = [];
        while ($news = $request->fetch(PDO::FETCH_ASSOC)) {
            $newsList[] = new News($news);
        }
        return $newsList;
    }

    // Récupérer une news par ID
    public function getNewsById(int $id): ?News
    {
        $sql = "SELECT * FROM news WHERE id = :id";

        $params = [
            ':id' => $id
        ];

        $request = $this->pdo->prepare($sql);
        $request->execute($params);
        $data = $request->fetch(PDO::FETCH_ASSOC);

        return $data ? new News($data) : null;
    }

    // Créer une news
    public function createNews(News $news): bool
    {

        $sql = "INSERT INTO news (titre, contenu, created_by, image)
            VALUES (:titre, :contenu, :created_by, :image)";

        $params = [
            ':titre' => $news->getTitre(),
            ':contenu' => $news->getContenu(),
            ':created_by' => $news->getCreatedBy(),
            ':image' => $news->getImage()
        ];

        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }

    // Mettre à jour une news
    public function updateNews(News $news): bool
    {
        $sql = "UPDATE news
            SET titre = :titre, contenu = :contenu, image = :image
            WHERE id = :id";

        $params = [
            ':titre' => $news->getTitre(),
            ':contenu' => $news->getContenu(),
            ':id' => $news->getId(),
            ':image' => $news->getImage(),
        ];

        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }

    // Supprimer une news
    public function deleteNews(int $id): bool
    {
        // Supprimer le fichier image avant suppression
        $news = $this->getNewsById($id);
        if ($news && $news->getImage()) {
            $filePath = __DIR__ . '/../../Public/uploads/news/' . $news->getImage();
            if (file_exists($filePath)) unlink($filePath);
        }
        $sql = "DELETE FROM news WHERE id = :id";

        $params = [
            ':id' => $id
        ];

        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }

    // Liste des news avec pagination
    public function findAllWithPagination(int $limit = 5, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
        SELECT * FROM news
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $newsWithAuthors = [];
        foreach ($newsList as $data) {
            $news = new News($data);
            $author = $this->userManager->findById($news->getCreatedBy());
            $newsWithAuthors[] = ['news' => $news, 'author' => $author];
        }

        $totalStmt = $this->pdo->query("SELECT COUNT(*) FROM news");
        $totalRows = (int)$totalStmt->fetchColumn();
        $totalPages = (int)ceil($totalRows / $limit);

        return [
            'newsWithAuthors' => $newsWithAuthors,
            'totalPages'      => $totalPages,
        ];
    }

    public function getPreviousNewsId(int $currentId): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM news WHERE id < :id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':id' => $currentId]);
        return $stmt->fetchColumn() ?: null;
    }

    public function getNextNewsId(int $currentId): ?int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM news WHERE id > :id ORDER BY id ASC LIMIT 1");
        $stmt->execute([':id' => $currentId]);
        return $stmt->fetchColumn() ?: null;
    }
}
