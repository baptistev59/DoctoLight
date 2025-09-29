<?php

class DisponibiliteStaff
{
    private ?int $id;
    private int $staff_id;
    private DateTime $start_time;
    private DateTime $end_time;
    private string $jour_semaine; // Lundi, Mardi, etc.

    public function __construct(?int $id, int $staff_id, DateTime $start_time, DateTime $end_time, string $jour_semaine)
    {
        $this->id = $id;
        $this->staff_id = $staff_id;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->jour_semaine = strtoupper($jour_semaine);
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getStaffId(): int
    {
        return $this->staff_id;
    }
    public function getStartTime(): DateTime
    {
        return $this->start_time;
    }
    public function getEndTime(): DateTime
    {
        return $this->end_time;
    }
    public function getJourSemaine(): string
    {
        return $this->jour_semaine;
    }

    // Méthodes utiles pour RDVController
    public function getStart(): string
    {
        return $this->start_time->format('H:i:s');
    }
    public function getEnd(): string
    {
        return $this->end_time->format('H:i:s');
    }

    // Setters
    public function setId(?int $id): void
    {
        $this->id = $id;
    }
    public function setStaffId(int $staff_id): void
    {
        $this->staff_id = $staff_id;
    }
    public function setStartTime(DateTime $start_time): void
    {
        $this->start_time = $start_time;
    }
    public function setEndTime(DateTime $end_time): void
    {
        $this->end_time = $end_time;
    }
    public function setJourSemaine(string $jour_semaine): void
    {
        $this->jour_semaine = strtoupper($jour_semaine);
    }
}
