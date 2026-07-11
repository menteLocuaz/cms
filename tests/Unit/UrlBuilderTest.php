<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Support\UrlBuilder;
use PHPUnit\Framework\TestCase;

final class UrlBuilderTest extends TestCase
{
    public function testApiWithEmptyParamsReturnsPlainPath(): void
    {
        $this->assertSame('admins', UrlBuilder::api('admins'));
    }

    public function testApiEncodesParams(): void
    {
        $url = UrlBuilder::api('admins', ['id' => 1, 'nameId' => 'id_admin']);

        $this->assertStringContainsString('id=1', $url);
        $this->assertStringContainsString('nameId=id_admin', $url);
    }

    public function testApiFullResolvesBaseUrl(): void
    {
        $url = UrlBuilder::apiFull('admins?login=true');

        $this->assertMatchesRegularExpression('#^https?://#', $url);
        $this->assertStringContainsString('admins?login=true', $url);
    }
}
