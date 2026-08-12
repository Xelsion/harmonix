<?php

namespace lib\core;

use JsonException;
use lib\core\classes\KeyValuePairs;
use lib\core\exceptions\SystemException;
use lib\helper\HtmlHelper;

/**
 * The Request Type setAsSingleton
 * represents the requested URL
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Request extends KeyValuePairs {

	public string $request_uri;
	public string $request_method;

	/**
	 * The class constructor
	 * sets the current requested uri
	 * calls the method initController()
	 * @throws \lib\core\exceptions\SystemException
	 */
	public function __construct() {
		$this->request_method = isset($_POST['request_method']) ? strtoupper($_POST['request_method']) : ($_SERVER['REQUEST_METHOD'] ?? "");
		if( $this->isInputAllowed() ) {
			$this->collectInputData();
		}
		$this->request_uri = $this->getCleanedRequestURI();
	}

	/**
	 * Checks if the input is allowed
	 *
	 * @return bool
	 */
	private function isInputAllowed(): bool {
		if( isset($_SESSION['csrf_token']) && in_array($this->request_method, [
				'POST',
				'PUT',
				'DELETE',
				'PATCH'
			], true) ) {
			if( isset($_POST['csrf_token']) && HtmlHelper::validateFormToken($_POST['csrf_token']) ) {
				HtmlHelper::deleteFormToken();
			} else {
				return false;
			}
		}
		return true;
	}

	/**
	 * Collects the form data from the request
	 *
	 * @param bool $accept_input
	 * @return void
	 * @throws SystemException
	 */
	private function collectInputData(): void {
		foreach( $_GET as $key => $value ) {
			$this->set($key, $value);
		}
		foreach( $_POST as $key => $value ) {
			$this->set($key, $value);
		}
		foreach( $_FILES as $key => $value ) {
			$this->set($key, $value);
		}
		$contentType = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? '';
		$json_str = trim(file_get_contents('php://input'));
		$isJSONHeader = str_contains(strtolower($contentType), "application/json");
		$isJSONContent = (str_starts_with($json_str, "{") || str_starts_with($json_str, "["));
		if( $json_str !== "" && ($isJSONHeader || $isJSONContent) ) {
			try {
				$json_obj = json_decode($json_str, true, 512, JSON_THROW_ON_ERROR);
				$this->set('json', $json_obj);
			} catch( JsonException $e ) {
				throw new SystemException(__FILE__, __LINE__, $e->getMessage());
			}
		}
	}

	/**
	 * Clears the request uri from query strings
	 *
	 * @return string
	 */
	private function getCleanedRequestURI(): string {
		$uri = $_SERVER["REQUEST_URI"] ?? "";
		return explode("?", $uri)[0];
	}

	/**
	 * Returns the requested uri
	 *
	 * @param string $uri
	 * @return void
	 */
	public function setRequestUri(string $uri): void {
		$this->request_uri = $uri;
	}

	/**
	 * Returns the requested uri
	 *
	 * @return string
	 */
	public function getRequestUri(): string {
		return $this->request_uri;
	}

	/**
	 * Returns the requested method
	 *
	 * @return string
	 */
	public function getRequestMethod(): string {
		return $this->request_method;
	}

	/**
	 * Returns the remote IP address
	 *
	 * @return string
	 */
	public function getRemoteIP(): string {
		return $_SERVER["REMOTE_ADDR"];
	}

	/**
	 * Split the requested uri into parts and
	 * returns them as an array
	 *
	 * @return array
	 */
	public function getRequestParts(): array {
		return explode('/', $this->getRequestUri())
				|> array_filter(...)
				|> array_values(...);
	}
}
