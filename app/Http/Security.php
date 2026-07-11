<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Helpers de seguridad: CSRF, tokens firmados, hashing.
 */
final class Security
{
    private const CSRF_KEY = '_csrf';
    private const TOKEN_TTL = 86400;

    /*=============================================
     * CSRF
     * =============================================*/

    public static function ensureCsrf(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return '';
        }

        if (empty($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::CSRF_KEY];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="_csrf" value="'
            . htmlspecialchars(self::ensureCsrf(), ENT_QUOTES, 'UTF-8')
            . '">';
    }

    public static function validateCsrf(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $sessionToken = $_SESSION[self::CSRF_KEY] ?? '';
        $postedToken = $_POST[self::CSRF_KEY] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

        if (!is_string($sessionToken) || $sessionToken === '' || !is_string($postedToken) || $postedToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $postedToken);
    }

    public static function requireCsrf(): void
    {
        if (!self::validateCsrf()) {
            http_response_code(403);
            exit('CSRF token mismatch');
        }
    }

    /*=============================================
     * Tokens firmados para IDs
     * =============================================*/

    public static function signId(int|string $id): string
    {
        $payload = $id . '|' . (time() + self::TOKEN_TTL);
        $signature = self::signature($payload);

        return base64_encode($payload . '|' . $signature);
    }

    public static function verifyId(string $token): int|string|null
    {
        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            return null;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            return null;
        }

        [$id, $exp, $signature] = $parts;
        $expected = self::signature($id . '|' . $exp);

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        if ((int) $exp < time()) {
            return null;
        }

        return ctype_digit((string) $id) ? (int) $id : $id;
    }

    /*=============================================
     * Hashing
     * =============================================*/

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /*=============================================
     * Auth gate para endpoints AJAX
     * =============================================*/

    public static function requireAdminAjax(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['admin'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 401, 'results' => 'Unauthorized']);
            exit;
        }
    }

    /*=============================================
     * Helpers internos
     * =============================================*/

    private static function signature(string $payload): string
    {
        $secret = $_ENV['APP_KEY']
            ?? $_ENV['API_TOKEN']
            ?? getenv('APP_KEY')
            ?: getenv('API_TOKEN')
            ?: 'change-me-in-env';

        return hash_hmac('sha256', $payload, $secret);
    }

    /*=============================================
     * Validación de identificadores SQL
     * =============================================*/

    public static function isValidIdentifier(string $value): bool
    {
        return (bool) preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{0,62}$/', $value);
    }
}
