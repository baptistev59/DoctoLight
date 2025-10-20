<?php

declare(strict_types=1);

class ServiceController extends BaseController
{

    public function __construct(
        PDO $pdo,
        ?ServiceManager $serviceManager = null,
        ?AuthController $authController = null
    ) {
        parent::__construct($pdo, $serviceManager, $authController);
    }

    // Liste des services
    public function list(): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE', 'MEDECIN']);

        $pageNum = max(1, (int)($_GET['page_num'] ?? 1));
        $perPage = 5;
        $offset = ($pageNum - 1) * $perPage;

        $search = trim($_GET['search'] ?? '');
        $sort = $_GET['sort'] ?? 'nom';
        $order = strtoupper($_GET['order'] ?? 'ASC');

        $result = $this->serviceManager->getFilteredServices($search, $sort, $order, $perPage, $offset);

        $services = $result['services'];
        $totalPages = (int)ceil($result['totalRows'] / $perPage);

        include __DIR__ . '/../Views/services/list.php';
    }

    // Formulaire de création
    public function create(): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);
        include __DIR__ . '/../Views/services/create.php';
    }

    // Enregistrer un nouveau service
    public function store(): void
    {
        $this->authController->checkCsrfToken();

        $nom = trim($_POST['nom'] ?? '');
        $duree = (int)($_POST['duree'] ?? 30);
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (strlen($nom) < 2) {
            $_SESSION['error'] = "Le nom du service est trop court.";
            header("Location: index.php?page=services_create");
            exit;
        }

        // --- Gestion de l’image ---
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../../Public/uploads/services/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $fileTmp = $_FILES['image']['tmp_name'];
            $originalName = basename($_FILES['image']['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // Vérification de l’extension
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($extension, $allowedExt)) {
                $_SESSION['error'] = "Format d'image non autorisé (JPG, PNG, WEBP uniquement).";
                header("Location: index.php?page=services_create");
                exit;
            }
            // Renommage du fichier
            $imageName = uniqid('service_') . '.' . $extension;

            // Déplacement du fichier
            if (!move_uploaded_file($fileTmp, $uploadDir . $imageName)) {
                $_SESSION['error'] = "Erreur lors de l'upload de l'image.";
                header("Location: index.php?page=services_create");
                exit;
            }
        }

        // Création du service
        $service = new Service([
            'nom' => $nom,
            'duree' => $duree,
            'description' => $description,
            'is_active' => $is_active
        ]);

        if ($this->serviceManager->createService($service)) {
            $_SESSION['success'] = "Service créé avec succès.";

            // AUDIT
            $this->audit('services', $service->getId(), 'INSERT', "Création du service « {$service->getNom()} »");
        } else {
            $_SESSION['error'] = "Erreur lors de la création du service.";
        }
        header("Location: index.php?page=services");
        exit;
    }

    // Formulaire d’édition
    public function edit(int $id): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);
        $service = $this->serviceManager->getServiceById($id);
        if (!$service) {
            $_SESSION['error'] = "Service introuvable.";
            header("Location: index.php?page=services");
            exit;
        }
        include __DIR__ . '/../Views/services/edit.php';
    }

    // Mettre à jour
    public function update(int $id): void
    {
        // var_dump($_FILES);
        // exit;

        // Vérification du CSRF token
        $this->authController->checkCsrfToken();

        // Données principales
        $nom = trim($_POST['nom'] ?? '');
        $duree = (int)($_POST['duree'] ?? 30);
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']);

        // on récupère le service à modifier
        $service = $this->serviceManager->getServiceById($id);
        if (!$service) {
            $_SESSION['error'] = "Service introuvable.";
            header("Location: index.php?page=services");
            exit;
        }

        // Vérification image (si nouvelle image uploadée)
        $imageName = $service->getImage(); // image actuelle par défaut

        // Si une nouvelle image a été envoyée
        // echo '<pre>';
        // var_dump($_FILES);
        // echo '</pre>';
        // exit;
        if (!empty($_FILES['new_image']['name'])) {
            // var_dump($_FILES['new_image']);
            // exit;

            $uploadDir = __DIR__ . '/../../Public/uploads/services/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $ext = strtolower(pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $newImage = uniqid('news_') . '.' . strtolower($ext);

            if (!in_array($ext, $allowed)) {
                $_SESSION['error'] = "Format d'image non valide (JPG, PNG, WEBP uniquement).";
                header("Location: index.php?page=services_edit&id=$id");
                exit;
            }

            $newImage = uniqid('news_') . '.' . strtolower($ext);

            if (move_uploaded_file($_FILES['new_image']['tmp_name'], $uploadDir . $newImage)) {
                // Supprime l’ancienne image si elle existe
                if ($imageName && file_exists($uploadDir . $imageName)) {
                    unlink($uploadDir . $imageName);
                }
                $imageName = $newImage;
            } else {
                $_SESSION['error'] = "Erreur lors de l'upload de l'image.";
                header("Location: index.php?page=services_edit&id=$id");
                exit;
            }
        }

        $service->setNom($nom);
        $service->setDescription($description);
        $service->setDuree($duree);
        $service->setActive($is_active);
        $service->setImage($imageName);

        if ($this->serviceManager->updateService($service)) {
            $_SESSION['success'] = "Service mis à jour avec succès.";

            // AUDIT
            $this->audit('services', $service->getId(), 'UPDATE', "Modification du service « {$service->getNom()} »");

            header("Location: index.php?page=services");
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du service.";
            header("Location: index.php?page=services_edit&id=$id");
            exit;
        }
    }

    // Suppression
    public function delete(int $id): void
    {
        // Vérification du CSRF token
        $this->authController->checkCsrfToken();

        $service = $this->serviceManager->getServiceById($id);

        if ($this->serviceManager->deleteService($id)) {

            if ($service && $service->getImage()) {
                $uploadDir = __DIR__ . '/../../Public/uploads/services/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
                $filePath = $uploadDir . $service->getImage();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $_SESSION['success'] = "Service supprimé avec succès.";

            // AUDIT
            $this->audit('services', $id, 'DELETE', "Suppression du service « {$service->getNom()} »");
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du service.";
        }
        header("Location: index.php?page=services");
        exit;
    }

    public function show(int $id): void
    {
        // Récupération du service
        $service = $this->serviceManager->getServiceById($id);

        if (!$service) {
            $_SESSION['error'] = "Service introuvable.";
            header("Location: index.php?page=services");
            exit;
        }

        // Récupération des disponibilités associées
        $dispos = $this->dispoServiceManager->getDisponibilitesByService($id);

        $joursOrdre = ['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE'];

        usort($dispos, function ($a, $b) use ($joursOrdre) {
            $posA = array_search($a->getJourSemaine(), $joursOrdre);
            $posB = array_search($b->getJourSemaine(), $joursOrdre);

            if ($posA === $posB) {
                return $a->getStartTime() <=> $b->getStartTime();
            }
            return $posA <=> $posB;
        });

        // Rendre disponible les infos de l'utilisateur connecté
        $currentUser = $_SESSION['user'] ?? null;
        $currentRoles = $currentUser ? $currentUser->getRoles() : [];

        include __DIR__ . '/../Views/services/show.php';
    }

    public function toggleActive(int $id): void
    {
        $this->authController->checkCsrfToken();
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE']);

        $service = $this->serviceManager->getServiceById($id);
        if (!$service) {
            $_SESSION['error'] = "Service introuvable.";
            header("Location: index.php?page=services");
            exit;
        }

        $newStatus = !$service->isActive();
        $service->setActive($newStatus);

        $this->serviceManager->updateService($service);

        $_SESSION['success'] = "Le service « {$service->getNom()} » a été " . ($newStatus ? "activé" : "désactivé") . ".";

        // AUDIT
        $this->audit(
            'services',
            $service->getId(),
            $newStatus ? 'ACTIVATE' : 'DEACTIVATE',
            ($newStatus
                ? "Activation du service « {$service->getNom()} »"
                : "Désactivation du service « {$service->getNom()} »")
        );

        header("Location: index.php?page=services");
        exit;
    }
}
