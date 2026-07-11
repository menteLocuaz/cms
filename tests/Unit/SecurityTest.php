<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Http\Security;
use PHPUnit\Framework\TestCase;

final class SecurityTest extends TestCase
{
    public function testHashPasswordProducesBcrypt(): void
    {
        $hash = Security::hashPassword('super-secret');

        $this->assertNotSame('super-secret', $hash);
        $this->assertSame(60, strlen($hash));
        $this->assertTrue(Security::verifyPassword('super-secret', $hash));
        $this->assertFalse(Security::verifyPassword('wrong', $hash));
    }

    public function testValidIdentifierAcceptsLettersUnderscoresAndDigits(): void
    {
        $this->assertTrue(Security::isValidIdentifier('admins'));
        $this->assertTrue(Security::isValidIdentifier('id_admin'));
        $this->assertTrue(Security::isValidIdentifier('order123'));
    }

    public function testValidIdentifierRejectsInjectionAttempts(): void
    {
        $this->assertFalse(Security::isValidIdentifier('admins; DROP TABLE x'));
        $this->assertFalse(Security::isValidIdentifier('admins--'));
        $this->assertFalse(Security::isValidIdentifier('1admins'));
        $this->assertFalse(Security::isValidIdentifier(''));
        $this->assertFalse(Security::isValidIdentifier('a;b'));
    }

    public function testSignIdRoundTrip(): void
    {
        $signed = Security::signId(42);
        $this->assertIsString($signed);
        $this->assertSame(42, Security::verifyId($signed));
    }

    public function testVerifyIdRejectsTamperedTokens(): void
    {
        $signed = Security::signId(42);
        $tampered = substr($signed, 0, -4) . 'AAAA';

        $this->assertNull(Security::verifyId($tampered));
    }

    public function testVerifyIdRejectsGarbage(): void
    {
        $this->assertNull(Security::verifyId('not-base64!@#'));
        $this->assertNull(Security::verifyId(base64_encode('foo|bar|baz')));
    }
}
