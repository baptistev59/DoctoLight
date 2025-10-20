<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testChargementDesClasses(): void
    {
        $this->assertTrue(class_exists('UserManager'));
        $this->assertTrue(class_exists('ServiceManager'));
        $this->assertTrue(class_exists('AuthController'));
    }

    public function testOperationBasique(): void
    {
        $this->assertSame(4, 2 + 2);
    }
}
