<?php
class HomeController
{
    public function index(): void
    {
        $serviceManager = new ServiceManager($this->pdo, $this->config);
        $dispoServiceManager = new DisponibiliteServiceManager($this->pdo);

        $services = $serviceManager->getAllServices();
        $horaires = $this->calculerHorairesCabinet($dispoServiceManager);

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
