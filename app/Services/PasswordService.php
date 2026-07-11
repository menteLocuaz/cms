<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

/**
 * Genera contraseñas aleatorias criptográficamente seguras.
 */
final class PasswordService
{
    private const CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function generate(int $length = 12): string
    {
        if ($length < 1) {
            throw new InvalidArgumentException('Password length must be at least 1');
        }

        $characters = self::CHARACTERS;
        $maxIndex = strlen($characters) - 1;
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $maxIndex)];
        }

        return $password;
    }
}
