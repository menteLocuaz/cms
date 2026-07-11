<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use App\Services\PasswordService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PasswordServiceTest extends TestCase
{
    public function testGenerateReturnsAlphanumericOfRequestedLength(): void
    {
        $password = (new PasswordService())->generate(11);

        $this->assertSame(11, strlen($password));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $password);
    }

    public function testGenerateUsesDefaultLengthWhenNotProvided(): void
    {
        $password = (new PasswordService())->generate();

        $this->assertSame(12, strlen($password));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $password);
    }

    public function testGenerateProducesUniqueValues(): void
    {
        $service = new PasswordService();
        $samples = [];

        for ($i = 0; $i < 20; $i++) {
            $samples[] = $service->generate(16);
        }

        $this->assertCount(20, array_unique($samples));
    }

    public function testGenerateRejectsZeroLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PasswordService())->generate(0);
    }

    public function testGenerateRejectsNegativeLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PasswordService())->generate(-5);
    }
}
