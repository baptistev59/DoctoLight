<?php

declare(strict_types=1);

class AuditLog
{
    private ?int $id;
    private string $tableName;
    private int $entityId;
    private string $action;
    private ?string $description;
    private string $actionDate;
    private ?int $userId;
    private ?string $ipAddress;

    public function __construct(
        ?int $id = null,
        string $tableName = '',
        int $entityId = 0,
        string $action = 'INSERT',
        ?string $description = null,
        string $actionDate = '',
        ?int $userId = null,
        ?string $ipAddress = null
    ) {
        $this->id = $id;
        $this->tableName = $tableName;
        $this->entityId = $entityId;
        $this->action = strtoupper($action);
        $this->description = $description;
        $this->actionDate = $actionDate;
        $this->userId = $userId;
        $this->ipAddress = $ipAddress;
    }

    // --- Getters ---
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTableName(): string
    {
        return $this->tableName;
    }
    public function getEntityId(): int
    {
        return $this->entityId;
    }
    public function getAction(): string
    {
        return $this->action;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function getActionDate(): string
    {
        return $this->actionDate;
    }
    public function getUserId(): ?int
    {
        return $this->userId;
    }
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    // --- Setters (si besoin) ---
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
