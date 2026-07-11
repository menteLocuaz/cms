<?php

declare(strict_types=1);

namespace App\Controllers;

class CurlController
{
    private const CONNECT_TIMEOUT = 3;
    private const REQUEST_TIMEOUT = 10;
    private const MAX_RETRIES = 1;
    private const RETRYABLE_HTTP = [502, 503, 504];

    /*=============================================
     * Peticiones a la API
     * =============================================*/

    public static function request($url, $method, $fields)
    {
        $baseUrl = self::resolveBaseUrl();
        $token = self::resolveToken();
        $fullUrl = rtrim($baseUrl, '/') . '/' . ltrim((string) $url, '/');

        $attempts = 0;
        $lastError = null;
        $lastStatus = 0;
        $startedAt = microtime(true);
        $remaining = self::MAX_RETRIES + 1;

        while ($remaining > 0) {
            $attempts++;
            $remaining--;

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $fullUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
                CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => is_array($fields) ? http_build_query($fields) : $fields,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json',
                    'Expect:',
                ],
            ]);

            $response = curl_exec($curl);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($response !== false && $response !== '') {
                $decoded = json_decode($response);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $latencyMs = (int) ((microtime(true) - $startedAt) * 1000);
                    error_log(sprintf(
                        'CurlController::request OK status=%d latency_ms=%d attempts=%d url=%s',
                        $httpCode,
                        $latencyMs,
                        $attempts,
                        $url,
                    ));

                    return $decoded;
                }

                $lastError = self::extractErrorMessage((string) $response);
                $lastStatus = 500;

                error_log(sprintf(
                    'CurlController::request JSON decode error: %s | Body: %s',
                    json_last_error_msg(),
                    substr((string) $response, 0, 200),
                ));

                break;
            }

            $lastError = $curlError !== '' ? $curlError : 'No response from API';
            $lastStatus = $curlErrno ? 0 : 500;

            error_log(sprintf(
                'CurlController::request failed (errno=%d, http=%d, attempt=%d): %s | URL: %s',
                $curlErrno,
                $httpCode,
                $attempts,
                $lastError,
                $url,
            ));

            if ($attempts >= self::MAX_RETRIES + 1 || !self::shouldRetry($curlErrno, $httpCode)) {
                break;
            }

            usleep(100000 * $attempts);
        }

        return (object) [
            'status' => $lastStatus ?: 500,
            'results' => 'Error: ' . $lastError,
        ];
    }

    /*=============================================
     * Peticiones a la API de ChatGPT
     * =============================================*/

    public static function chatGPT($content, $token, $org)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::REQUEST_TIMEOUT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'gpt-4-0613',
                'messages' => [['role' => 'user', 'content' => $content]],
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'OpenAI-Organization: ' . $org,
                'Content-Type: application/json',
                'Expect:',
            ],
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $curlErrno = curl_errno($curl);

        if ($response === false || $response === '') {
            error_log(sprintf('CurlController::chatGPT failed (errno=%d): %s', $curlErrno, $curlError));
            return null;
        }

        $decoded = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log(sprintf(
                'CurlController::chatGPT JSON decode error: %s | Body: %s',
                json_last_error_msg(),
                substr((string) $response, 0, 200),
            ));
            return null;
        }

        if (!isset($decoded->choices[0]->message->content)) {
            error_log('CurlController::chatGPT unexpected response structure: ' . substr((string) $response, 0, 200));
            return null;
        }

        return $decoded->choices[0]->message->content;
    }

    /*=============================================
     * Helpers privados
     * =============================================*/

    private static function resolveBaseUrl(): string
    {
        $url = $_ENV['API_BASE_URL']
            ?? $_ENV['CURL_API_URL']
            ?? getenv('API_BASE_URL')
            ?: getenv('CURL_API_URL')
            ?: 'http://localhost:9090/';

        return $url;
    }

    private static function resolveToken(): string
    {
        return (string) (
            $_ENV['API_TOKEN']
            ?? $_ENV['CURL_API_TOKEN']
            ?? getenv('API_TOKEN')
            ?: getenv('CURL_API_TOKEN')
            ?: ''
        );
    }

    private static function shouldRetry(int $errno, int $httpCode): bool
    {
        if (in_array($errno, [CURLE_OPERATION_TIMEDOUT, CURLE_COULDNT_CONNECT, CURLE_RECV_ERROR], true)) {
            return true;
        }

        return in_array($httpCode, self::RETRYABLE_HTTP, true);
    }

    /*=============================================
     * Extrae un mensaje legible de una respuesta no-JSON
     * (fatal de PHP, error de PDO, página HTML, etc.)
     * =============================================*/

    private static function extractErrorMessage(string $body): string
    {
        if (preg_match(
            '/SQLSTATE\[([^\]]+)\]:\s*(?:Undefined table|relation)[^<]*?(\w+)\s+does not exist/i',
            $body,
            $m,
        )) {
            return 'Error: table "' . $m[2] . '" does not exist in the database';
        }
        if (preg_match('/SQLSTATE\[([^\]]+)\]:\s*([^<]+?)(?:\s+in\s|<br|$)/i', $body, $m)) {
            return 'Error: SQLSTATE[' . $m[1] . '] ' . trim($m[2]);
        }
        if (preg_match('/<b>(?:Fatal error|Parse error)<\/b>:\s*(.*?)(?:\s+in\s+|\s*<br|$)/is', $body, $m)) {
            return 'Error: ' . trim(strip_tags($m[1]));
        }
        if (preg_match('/<title>(.*?)<\/title>/is', $body, $m)) {
            return 'Error: ' . trim($m[1]);
        }
        return 'Error: invalid response from API';
    }
}
