<?php

declare(strict_types=1);

namespace App\Controllers;

class CurlController{

	/*=============================================
	Peticiones a la API
	=============================================*/	

	static public function request($url,$method,$fields){

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'http://api.pos.com/'.$url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => $fields,
			CURLOPT_HTTPHEADER => array(
				'Authorization: gdfhdfhsdfyeryr34646fhdfy4564t3456fhgdy'
			),
		));

		$response = curl_exec($curl);
		$curlError = curl_error($curl);
		$curlErrno = curl_errno($curl);
		curl_close($curl);

		if ($response === false || $response === '') {
			error_log(sprintf(
				'CurlController::request failed (errno=%d): %s | URL: %s',
				$curlErrno,
				$curlError,
				$url
			));
			return null;
		}

		$decoded = json_decode($response);

		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log(sprintf(
				'CurlController::request JSON decode error: %s | Body: %s',
				json_last_error_msg(),
				substr($response, 0, 200)
			));
			return null;
		}

		return $decoded;

	}

	/*=============================================
	Peticiones a la API de ChatGPT
	=============================================*/	

	static public function chatGPT($content,$token,$org){

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
		  CURLOPT_POSTFIELDS =>'{
		    "model": "gpt-4-0613",
		    "messages":[{"role": "user", "content": "'.$content.'"}]
		}',
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Bearer '.$token,
		    'OpenAI-Organization: '.$org,
		    'Content-Type: application/json'
		  ),
		));

		$response = curl_exec($curl);
		$curlError = curl_error($curl);
		$curlErrno = curl_errno($curl);
		curl_close($curl);

		if ($response === false || $response === '') {
			error_log(sprintf(
				'CurlController::chatGPT failed (errno=%d): %s',
				$curlErrno,
				$curlError
			));
			return null;
		}

		$decoded = json_decode($response);

		if (json_last_error() !== JSON_ERROR_NONE) {
			error_log(sprintf(
				'CurlController::chatGPT JSON decode error: %s | Body: %s',
				json_last_error_msg(),
				substr($response, 0, 200)
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
