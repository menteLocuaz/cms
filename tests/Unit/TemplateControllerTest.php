<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Controllers\TemplateController;
use PHPUnit\Framework\TestCase;

final class TemplateControllerTest extends TestCase
{
    public function testGenPasswordReturnsAlphanumericOfRequestedLength(): void
    {
        $password = TemplateController::genPassword(11);

        $this->assertSame(11, strlen($password));
        $this->assertMatchesRegularExpression('/^[a-z0-9]+$/', $password);
    }

    public function testReduceTextShortensLongStrings(): void
    {
        $reduced = TemplateController::reduceText('abcdefghij', 5);

        $this->assertSame('abcde...', $reduced);
    }

    public function testReduceTextKeepsShortStringsIntact(): void
    {
        $this->assertSame('abc', TemplateController::reduceText('abc', 5));
    }
}
