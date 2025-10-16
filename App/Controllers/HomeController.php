<?php

declare(strict_types=1);

class HomeController extends BaseController
{

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function index(): void
    {
        $services = $this->serviceManager->getAllServices();
        $horaires = $this->calculerHorairesCabinet($this->dispoServiceManager);

        $latestNews = $this->newsManager->getLatest(3);
        $fermeturesActives = $this->fermetureManager->getActive();

        // $this->audit('test', 1, 'INSERT', 'Test depuis HomeController');
        include __DIR__ . '/../Views/home.php';
    }

    /**
     * Calcule les horaires d'ouverture du cabinet
     * en fonction des disponibilités de tous les services actifs.
     */
    private function calculerHorairesCabinet(DisponibiliteServiceManager $manager): array
    {
        $jours = ['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE'];
        $horaires = [];

        foreach ($jours as $jour) {
            $dispos = $manager->getAllDisponibilitesByJour($jour);

            if (empty($dispos)) {
                $horaires[$jour] = ['open' => null, 'close' => null];
                continue;
            }

            $earliest = min(array_map(fn($d) => $d->getStartTime()->format('H:i'), $dispos));
            $latest = max(array_map(fn($d) => $d->getEndTime()->format('H:i'), $dispos));

            $horaires[$jour] = ['open' => $earliest, 'close' => $latest];
        }

        return $horaires;
    }
}
