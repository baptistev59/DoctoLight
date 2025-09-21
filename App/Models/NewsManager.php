<?php
class NewsManager
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Récupérer toutes les news
    public function getAllNews(): array
    {
        $sql = "SELECT * FROM news ORDER BY created_at DESC";

        $request = $this->pdo->prepare($sql);
        $request->execute();

        $newsList = [];
        while ($row = $request->fetch(PDO::FETCH_ASSOC)) {
            $newsList[] = new News($row);
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

        $sql = "INSERT INTO news (titre, contenu, created_by) 
            VALUES (:titre, :contenu, :created_by)";

        $params = [
            ':titre' => $news->getTitre(),
            ':contenu' => $news->getContenu(),
            ':created_by' => $news->getCreatedBy()
        ];

        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }

    // Mettre à jour une news
    public function updateNews(News $news): bool
    {
        $sql = "UPDATE news 
            SET titre = :titre, contenu = :contenu 
            WHERE id = :id";

        $params = [
            ':titre' => $news->getTitre(),
            ':contenu' => $news->getContenu(),
            ':id' => $news->getId()
        ];

        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }

    // Supprimer une news
    public function deleteNews(int $id): bool
    {
        $sql = "DELETE FROM news WHERE id = :id";

        $params = [
            ':id' => $id
        ];

        $request = $this->pdo->prepare($sql);
        return $request->execute($params);
    }
}
