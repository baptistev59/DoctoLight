<?php
class NewsController
{
    private NewsManager $newsManager;
    private AuthController $authController;
    private UserManager $userManager;

    // include __DIR__ . '/../
    public function __construct(PDO $pdo)
    {
        $this->newsManager = new NewsManager($pdo);
        $this->authController = new AuthController($pdo);
        $this->userManager = new UserManager($pdo, []);
    }

    // Afficher toutes les news
    public function list()
    {
        $newsList = $this->newsManager->getAllNews();

        // Associer chaque news avec son auteur
        $newsWithAuthors = [];

        foreach ($newsList as $news) {
            $author = $this->userManager->findById($news->getCreatedBy());
            $newsWithAuthors[] = [
                'news'   => $news,
                'author' => $author
            ];
        }

        // rendre disponible $authController dans la vue
        $authController = $this->authController;

        include __DIR__ . '/../Views/news/list.php';
    }
    // Afficher une seule news
    public function show()
    {
        if (!isset($_GET['id'])) {
            header('Location: index.php?page=news');
            exit;
        }
        $id = intval($_GET['id']);
        $news = $this->newsManager->getNewsById($id);

        $author = $this->userManager->findById($news->getCreatedBy());

        if (!$news) {
            die("Actualité non trouvée !");
        }
        include __DIR__ . '/../Views/news/shows.php';
    }

    // Affiche les 5 dernères news
    public function getLatestNews($limit = 5): array
    {
        return $this->newsManager->getLatest($limit);
    }

    // Formulaire création news
    public function create()
    {
        include __DIR__ . '/../Views/news/create.php';
    }

    // Enregistrer une news
    public function createValid()
    {
        // var_dump($_POST);
        // die();
        // Vérification du CSRF token
        $this->authController->checkCsrfToken();

        // Utilisateur connecté
        $currentUser = $_SESSION['user'] ?? null;



        $titre = trim($_POST['titre']);
        $contenu = trim($_POST['contenu']);
        $createdBy = intval($currentUser->getId() ?? 1); // Désactive le login obligatoire);

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
                die("Erreur lors de la création de l'actualité.");
            }
        }
    }

    // Formulaire édition news
    public function editForm()
    {
        $id = intval($_GET['id']);
        $news = $this->newsManager->getNewsById($id);

        if (!$news) die("Actualité non trouvée !");
        include __DIR__ . '/../Views/news/edit.php';
    }

    // Mettre à jour une news
    public function update()
    {
        // Vérification du CSRF token
        $this->authController->checkCsrfToken();

        $id = intval($_GET['id']);
        $titre = trim($_POST['titre']);
        $contenu = trim($_POST['contenu']);

        $news = $this->newsManager->getNewsById($id);

        if (!$news) die("Acyualité non trouvée !");
        $news->setTitre($titre);
        $news->setContenu($contenu);

        if ($this->newsManager->updateNews($news)) {
            header("Location: index.php?page=news&success=updated");
        } else {
            die("Erreur lors de la mise à jour de l'actualité.");
        }
    }

    // Supprimer une news
    public function delete()
    {
        // Vérification du CSRF token
        $this->authController->checkCsrfToken();

        $id = intval($_POST['id'] ?? 0);
        if ($id && $this->newsManager->deleteNews($id)) {
            header("Location: index.php?page=news&success=deleted");
        } else {
            die("Erreur lors de la suppression de l'actualité.");
        }
    }
}
