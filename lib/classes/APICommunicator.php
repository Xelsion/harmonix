<?php

namespace lib\classes;

use CurlHandle;
use Exception;
use JsonException;
use lib\core\enums\RequestMethod;
use RuntimeException;

class APICommunicator {

	private CurlHandle $curl;

	private array $curl_headers = [];

	private string $host;

	private int $port;

	/**
	 * @param string $host
	 * @param int $port
	 * @param int $timeout
	 */
	public function __construct(string $host, int $port, int $timeout = 30) {
		$this->host = $host;
		$this->port = $port;

		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_TIMEOUT, $timeout);
		$this->curl_headers[1] = 'Content-Type: application/json';
	}

	/**
	 * @return void
	 */
	public function disableSSLVerify(): void {
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function setAuth(string $key, string $value): void {
		$this->curl_headers[0] = $key . ': ' . $value;
	}

	/**
	 * @param string $url
	 * @param RequestMethod $method
	 * @param object|array|null $data
	 * @return mixed
	 */
	public function call(string $url, RequestMethod $method = RequestMethod::GET, object|array $data = null): mixed {
		try {
			if( !str_starts_with($url, "/") ) {
				$url = '/' . $url;
			}
			$call_url = $this->host . $url;
			curl_setopt($this->curl, CURLOPT_URL, $call_url);
			curl_setopt($this->curl, CURLOPT_PORT, $this->port);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->curl_headers);
			switch( $method ) {
				case RequestMethod::DELETE:
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
					break;
				case RequestMethod::POST:
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
					break;
				case RequestMethod::PUT:
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
					break;
				default:
					curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");
					break;
			}

			if( !is_null($data) ) {
				curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
			}
			$response = curl_exec($this->curl);
			curl_close($this->curl);

			if( $response && self::isJson($response) ) {
				$response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
			} else {
				throw new RuntimeException("API unavailable");
			}
			return $response;
		} catch( Exception ) {
			return null;
		}
	}

	/**
	 * @param string $string
	 * @return bool
	 */
	public static function isJson(string $string): bool {
		try {
			json_decode($string, true, 512, JSON_THROW_ON_ERROR);
			return json_last_error() === JSON_ERROR_NONE;
		} catch( JsonException ) {
			return false;
		}
	}

}