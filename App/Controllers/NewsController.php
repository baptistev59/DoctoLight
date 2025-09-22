<?php
class NewsController
{
    private NewsManager $newsManager;
    // include __DIR__ . '/../
    public function __construct(PDO $pdo)
    {
        $this->newsManager = new NewsManager($pdo);
    }

    // Afficher toutes les news
    public function list()
    {
        $newsList = $this->newsManager->getAllNews();
        include __DIR__ . '/../Views/news/list.php';
    }

    // Afficher une seule news
    public function show()
    {
        $id = intval($_GET['id']);
        $news = $this->newsManager->getNewsById($id);
        if (!$news) {
            die("News non trouvée !");
        }
        include __DIR__ . '/../Views/news/shows.php';
    }

    // Formulaire création news
    public function create()
    {
        include __DIR__ . '/../Views/news/create.php';
    }

    // Enregistrer une news
    public function createValid()
    {
        $titre = trim($_POST['titre']);
        $contenu = trim($_POST['contenu']);
        $createdBy = intval($_SESSION['user_id'] ?? 1); // Désactive le login obligatoire);

        $news = new News([
            'titre' => $titre,
            'contenu' => $contenu,
            'created_by' => $createdBy
        ]);

        if (strlen($titre) < 3 || strlen($contenu) < 3) {
            header('Location: index.php?page=create-news&error=validation');
        } else {
            if ($this->newsManager->createNews($news)) {
                header("Location: index.php?page=news&success=created");
            } else {
                die("Erreur lors de la création de la news.");
            }
        }
    }

    // Formulaire édition news
    public function editForm()
    {
        $id = intval($_GET['id']);
        $news = $this->newsManager->getNewsById($id);
        if (!$news) die("News non trouvée !");
        include __DIR__ . '/../Views/news/edit.php';
    }

    // Mettre à jour une news
    public function update()
    {
        $id = intval($_GET['id']);
        $titre = trim($_GET['titre']);
        $contenu = trim($_GET['contenu']);

        $news = $this->newsManager->getNewsById($id);

        if (!$news) die("News non trouvée !");
        $news->setTitre($_POST['titre']);
        $news->setContenu($_POST['contenu']);

        if ($this->newsManager->updateNews($news)) {
            header("Location: index.php?page=news&success=updated");
        } else {
            die("Erreur lors de la mise à jour de la news.");
        }
    }

    // Supprimer une news
    public function delete()
    {
        $id = $_GET['id'];
        if ($this->newsManager->deleteNews($id)) {
            header("Location: index.php?page=news&success=deleted");
        } else {
            die("Erreur lors de la suppression de la news.");
        }
    }
}
