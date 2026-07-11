<?php

declare(strict_types=1);

namespace App\Tests\Unit\Support;

use App\Support\StringHelper;
use PHPUnit\Framework\TestCase;

final class StringHelperTest extends TestCase
{
    public function testReduceShortensLongStrings(): void
    {
        $this->assertSame('abcde...', StringHelper::reduce('abcdefghij', 5));
    }

    public function testReduceKeepsShortStringsIntact(): void
    {
        $this->assertSame('abc', StringHelper::reduce('abc', 5));
    }

    public function testReduceHandlesMultibyteUtf8(): void
    {
        $this->assertSame('áéíóú...', StringHelper::reduce('áéíóúñáéíóúñ', 5));
    }

    public function testReduceAcceptsCustomSuffix(): void
    {
        $this->assertSame('abcde…', StringHelper::reduce('abcdefghij', 5, '…'));
    }
}
