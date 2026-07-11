<?php

declare(strict_types=1);

namespace App\Controllers;

class CurlController
{
    /*=============================================
     * Peticiones a la API
     * =============================================*/

    public static function request($url, $method, $fields)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://localhost:9090/' . ltrim($url, '/'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => is_array($fields) ? http_build_query($fields) : $fields,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Svgh8rc6TbSr3nPyY2TfKc&kM%K$nU38GFnCbYMw^qLF6jq%cCc@^Hyucz6Sy7Sq76sqs6qoNT4p8ADwVQwCCCD!Wb@3u^9svmheN5yrH6znhv65XyAFpGDp!CRT@aX$c9DsVBPn6xcX2$xJM9crxEFigTc',
            ),
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $curlErrno = curl_errno($curl);
        curl_close($curl);

        if ($response === false || $response === '') {
            error_log(sprintf('CurlController::request failed (errno=%d): %s | URL: %s', $curlErrno, $curlError, $url));
            return (object) ['status' => 500, 'results' => 'Error: ' . ($curlError ?: 'No response from API')];
        }

        $decoded = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log(sprintf(
                'CurlController::request JSON decode error: %s | Body: %s',
                json_last_error_msg(),
                substr($response, 0, 200),
            ));
            return (object) ['status' => 500, 'results' => self::extractErrorMessage($response)];
        }

        return $decoded;
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

    /*=============================================
     * Peticiones a la API de ChatGPT
     * =============================================*/

    public static function chatGPT($content, $token, $org)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
		    "model": "gpt-4-0613",
		    "messages":[{"role": "user", "content": "' . $content . '"}]
		}',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'OpenAI-Organization: ' . $org,
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $curlErrno = curl_errno($curl);
        curl_close($curl);

        if ($response === false || $response === '') {
            error_log(sprintf('CurlController::chatGPT failed (errno=%d): %s', $curlErrno, $curlError));
            return null;
        }

        $decoded = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log(sprintf(
                'CurlController::chatGPT JSON decode error: %s | Body: %s',
                json_last_error_msg(),
                substr($response, 0, 200),
            ));
            return null;
        }

        if (!isset($decoded->choices[0]->message->content)) {
            error_log('CurlController::chatGPT unexpected response structure: ' . substr($response, 0, 200));
            return null;
        }

        return $decoded->choices[0]->message->content;
    }
}
