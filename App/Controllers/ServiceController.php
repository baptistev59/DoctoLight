<?php
class ServiceController
{
    private ServiceManager $serviceManager;
    private AuthController $authController;

    public function __construct(PDO $pdo)
    {
        $this->serviceManager = new ServiceManager($pdo);
        $this->authController = new AuthController($pdo);
    }

    // Liste des services
    public function list(): void
    {
        $this->authController->requireRole(['ADMIN', 'SECRETAIRE', 'MEDECIN']);

        $services = $this->serviceManager->getAllServices();
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
        $this->authController->checkCsrfToken();

        $seriveUpdated = new Service([
            'id'          => $id,
            'nom' => trim($_POST['nom'] ?? ''),
            'duree' => (int)($_POST['duree'] ?? 30),
            'description' => trim($_POST['description'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ]);

        // Gestion d’image (nouvelle image)
        if (!empty($_FILES['new_image']['name'])) {
            $targetDir = __DIR__ . '/../../Public/uploads/services/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $ext = pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('srv_') . '.' . strtolower($ext);
            $targetFile = $targetDir . $filename;

            // Supprime l’ancienne image si elle existe
            if ($seriveUpdated->getImage() && file_exists($targetDir . $seriveUpdated->getImage())) {
                unlink($targetDir . $seriveUpdated->getImage());
            }

            if (move_uploaded_file($_FILES['new_image']['tmp_name'], $targetFile)) {
                $seriveUpdated->setImage($filename);
            }
        }

        if ($this->serviceManager->updateService($seriveUpdated)) {
            $_SESSION['success'] = "Service mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du service.";
        }
        header("Location: index.php?page=services");
        exit;
    }

    // Suppression
    public function delete(int $id): void
    {
        $this->authController->checkCsrfToken();
        $service = $this->serviceManager->getServiceById($id);

        if ($this->serviceManager->deleteService($id)) {

            if ($service && $service->getImage()) {
                $uploadDir = __DIR__ . '/../../Public/uploads/services/';
                $filePath = $uploadDir . $service->getImage();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $_SESSION['success'] = "Service supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression du service.";
        }
        header("Location: index.php?page=services");
        exit;
    }
}
