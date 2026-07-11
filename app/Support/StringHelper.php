<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Utilidades de manipulación de cadenas.
 */
final class StringHelper
{
    /**
     * Trunca un texto a un número máximo de caracteres (multibyte-safe).
     * Si el texto excede el límite, se trunca y se concatena el sufijo.
     */
    public static function reduce(string $text, int $limit, string $suffix = '...'): string
    {
        return mb_strlen($text) > $limit
            ? mb_substr($text, 0, $limit) . $suffix
            : $text;
    }
}
