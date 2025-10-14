<?php
class HomeController
{
    private ServiceManager $serviceManager;
    private DisponibiliteServiceManager $dispoServiceManager;
    private NewsManager $newsManager;
    private FermetureManager $fermetureManager;

    public function __construct(PDO $pdo)
    {
        $this->serviceManager = new ServiceManager($pdo);
        $this->dispoServiceManager = new DisponibiliteServiceManager($pdo);
        $this->newsManager = new NewsManager($pdo);
        $this->fermetureManager = new FermetureManager($pdo);
    }

    public function index(): void
    {
        $services = $this->serviceManager->getAllServices();
        $horaires = $this->calculerHorairesCabinet($this->dispoServiceManager);

        $latestNews = $this->newsManager->getLatest(3);
        $fermeturesActives = $this->fermetureManager->getActive();


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
