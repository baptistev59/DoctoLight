<?php
class Rdv
{
    private int $id;
    private int $patientId;
    private int $staffId;
    private int $serviceId;
    private ?int $dispoStaffId;
    private ?int $dispoServiceId;
    private \DateTime $dateRdv;   // Date du rendez-vous (jour précis)
    private string $heureDebut;   // ex. "09:00:00"
    private string $heureFin;     // ex. "09:30:00"
    private int $duree;           // Durée en minutes
    private string $statut;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->patientId = $data['patient_id'];
        $this->staffId = $data['staff_id'];
        $this->serviceId = $data['service_id'];
        $this->dispoStaffId = $data['dispo_staff_id'] ?? null;
        $this->dispoServiceId = $data['dispo_service_id'] ?? null;
        $this->dateRdv = new \DateTime($data['date_rdv']);
        $this->duree = $data['duree'] ?? 30; // valeur par défaut
        $this->heureDebut = $data['heure_debut'] ?? $this->dateRdv->format('H:i:s');
        $this->heureFin = $data['heure_fin'] ?? (clone $this->dateRdv)->modify("+{$this->duree} minutes")->format('H:i:s');
        $this->statut = $data['statut'] ?? 'PROGRAMME';
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }
    public function getPatientId(): int
    {
        return $this->patientId;
    }
    public function getStaffId(): int
    {
        return $this->staffId;
    }
    public function getServiceId(): int
    {
        return $this->serviceId;
    }
    public function getDispoStaffId(): ?int
    {
        return $this->dispoStaffId;
    }
    public function getDispoServiceId(): ?int
    {
        return $this->dispoServiceId;
    }
    public function getDateRdv(): \DateTime
    {
        return $this->dateRdv;
    }
    public function getHeureDebut(): string
    {
        return $this->heureDebut;
    }
    public function getHeureFin(): string
    {
        return $this->heureFin;
    }
    public function getDuree(): int
    {
        return $this->duree;
    }
    public function getStatut(): string
    {
        return $this->statut;
    }

    // Setters
    public function setStatut(string $statut): void
    {
        $validStatuts = ['PROGRAMME', 'ANNULE', 'TERMINE'];
        if (!in_array($statut, $validStatuts, true)) {
            throw new InvalidArgumentException("Statut invalide : $statut. Valeurs possibles : " . implode(', ', $validStatuts));
        }
        $this->statut = $statut;
    }

    public function isProgramme(): bool
    {
        return $this->statut === 'PROGRAMME';
    }
    public function isAnnule(): bool
    {
        return $this->statut === 'ANNULE';
    }
    public function isTermine(): bool
    {
        return $this->statut === 'TERMINE';
    }
}
