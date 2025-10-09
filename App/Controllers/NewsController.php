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
    public function list(): void
    {
        // Récupération de la page actuelle
        $pageNum = max(1, (int)($_GET['page_num'] ?? 1));
        $perPage = 6; // nombre d’articles par page
        $offset = ($pageNum - 1) * $perPage;

        // Récupération des actualités paginées
        $result = $this->newsManager->findAllWithPagination($perPage, $offset);

        $newsWithAuthors = $result['newsWithAuthors'] ?? [];
        $totalPages = $result['totalPages'];

        // Rendre $authController disponible dans la vue
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

        $previousId = $this->newsManager->getPreviousNewsId($news->getId());
        $nextId = $this->newsManager->getNextNewsId($news->getId());

        if (!$news) {
            die("Actualité non trouvée !");
        }
        include __DIR__ . '/../Views/news/shows.php';
    }

    // Affiche les 9 dernères news
    public function getLatestNews($limit = 6): array
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
        $createdBy = intval($currentUser->getId() ?? 1);

        if (strlen($titre) < 3 || strlen($contenu) < 3) {
            header('Location: index.php?page=create-news&error=validation');
            exit;
        }

        // Upload de l'image
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../../Public/uploads/news/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('news_') . '.' . strtolower($ext);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }

        $news = new News([
            'titre' => $titre,
            'contenu' => $contenu,
            'created_by' => $createdBy,
            'image' => $imageName
        ]);

        if ($this->newsManager->createNews($news)) {
            header("Location: index.php?page=news&success=created");
        } else {
            die("Erreur lors de la création de l'actualité.");
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
        $imageName = $news->getImage();

        if (!$news) {
            $_SESSION['error'] = "Actualité introuvable.";
            header("Location: index.php?page=news");
            exit;
        }

        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../../Public/uploads/news/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];


            if (!in_array($ext, $allowed)) {
                $_SESSION['error'] = "Format d'image non valide (JPG, PNG, WEBP uniquement).";
                header("Location: index.php?page=services_edit&id=$id");
                exit;
            }

            $newImage = uniqid('news_') . '.' . strtolower($ext);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImage)) {
                // Supprime l’ancienne image si elle existe
                if ($imageName && file_exists($uploadDir . $imageName)) {
                    unlink($uploadDir . $imageName);
                }
                $imageName = $newImage;
            } else {
                $_SESSION['error'] = "Erreur lors de l'upload de l'image.";
                header("Location: index.php?page=news_edit&id=$id");
                exit;
            }
        }

        $news->setTitre($titre);
        $news->setContenu($contenu);
        $news->setImage($imageName);

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

        $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id && $this->newsManager->deleteNews($id)) {
            header("Location: index.php?page=news&success=deleted");
        } else {
            die("Erreur lors de la suppression de l'actualité.");
        }
    }
}
