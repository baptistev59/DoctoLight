<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

/**
 * Test du ServiceController sans accès à la base
 */
final class ServiceControllerTest extends TestCase
{
    public function testListAfficheServicesSansErreur(): void
    {
        // Simule un ServiceManager avec des données fixes
        $fakeServiceManager = $this->createMock(ServiceManager::class);
        $fakeServiceManager
            ->method('getFilteredServices')
            ->willReturn([
                'services' => [
                    (object)['nom' => 'Service Test 1'],
                    (object)['nom' => 'Service Test 2']
                ],
                'totalRows' => 2
            ]);

        // Mock AuthController
        $fakeAuth = $this->createMock(AuthController::class);
        $fakeAuth->method('requireRole')->willReturnCallback(function () {});

        // Fausse connexion PDO
        $pdo = $this->createMock(PDO::class);

        // Instanciation du contrôleur
        $controller = new ServiceController($pdo, $fakeServiceManager, $fakeAuth);

        // Capture la sortie
        ob_start();
        $controller->list();
        $output = ob_get_contents();
        ob_end_clean();

        // Vérifications
        $this->assertIsString($output);
        $this->assertStringContainsString('Service Test 1', $output);
        $this->assertStringNotContainsString('403', $output);
    }
}
