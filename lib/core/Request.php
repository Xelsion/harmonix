<?php

namespace lib\core;

use JsonException;
use lib\core\classes\KeyValuePairs;
use lib\core\enums\RequestMethod;
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
	public RequestMethod $request_method;

	/**
	 * The class constructor
	 * sets the current requested uri
	 * calls the method initController()
	 * @throws SystemException
	 */
	public function __construct() {
		if( isset($_POST['request_method']) ) {
			$this->request_method = RequestMethod::fromString($_POST['request_method']);
		} else if( isset($_SERVER['REQUEST_METHOD']) ) {
			$this->request_method = RequestMethod::fromString($_SERVER['REQUEST_METHOD']);
		} else {
			$this->request_method = RequestMethod::GET;
		}
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
		$protected_methods = ['POST', 'PUT', 'DELETE'];
		if( isset($_SESSION['csrf_token']) && in_array($this->request_method->toString(), $protected_methods, true) ) {
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
		return explode('/', $this->request_uri)
				|> array_filter(...)
				|> array_values(...);
	}
}
