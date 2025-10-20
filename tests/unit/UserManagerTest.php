<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test UserManager sans dépendance aux rôles
 */
final class UserManagerTest extends TestCase
{
    private PDO $pdo;
    private UserManager $userManager;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Tables minimales
        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT,
                prenom TEXT,
                email TEXT,
                mot_de_passe TEXT
            );
        ");

        // Faux UserManager
        $this->userManager = $this->getMockBuilder(UserManager::class)
            ->setConstructorArgs([$this->pdo, []])
            ->onlyMethods(['getRolesForUser'])
            ->getMock();

        // Simule une méthode fictive neutre
        $this->userManager->method('getRolesForUser')->willReturn([]);
    }

    public function testInsertionEtRecuperationUtilisateur(): void
    {
        $this->pdo->exec("
            INSERT INTO users (nom, prenom, email, mot_de_passe)
            VALUES ('Dupont', 'Jean', 'jean@example.com', 'hash')
        ");
        $id = (int)$this->pdo->lastInsertId();

        $user = $this->userManager->findById($id);

        $this->assertNotNull($user);
        $this->assertSame('Dupont', $user->getNom());
        $this->assertSame('Jean', $user->getPrenom());
    }
}
