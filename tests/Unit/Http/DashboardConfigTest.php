<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http;

use App\Http\DashboardConfig;
use PHPUnit\Framework\TestCase;

final class DashboardConfigTest extends TestCase
{
    public function testLoadReturnsNullWhenApiUnreachable(): void
    {
        // Con la API remota caída (entorno de tests sin servidor),
        // CurlController::request devuelve {status: 500}, y DashboardConfig
        // debe normalizar a null.
        $this->assertNull(DashboardConfig::load());
    }
}
