<?php

namespace lib\core;

use lib\core\classes\KeyValuePairs;
use lib\helper\HtmlHelper;

/**
 * The Request Type setAsSingleton
 * represents the requested URL
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Request extends KeyValuePairs {

	private string $request_uri;
	private string $request_method;

	/**
	 * The class constructor
	 * sets the current requested uri
	 * calls the method initController()
	 * @throws \lib\core\exceptions\SystemException
	 */
	public function __construct() {
		$accept_input = true;
		if( isset($_POST['csrf_token']) ) {
			if( !HtmlHelper::validateFormToken($_POST['csrf_token']) ) {
				$accept_input = false;
			}
			HtmlHelper::deleteFormToken();
		}

		if( $accept_input ) {
			foreach( $_GET as $key => $value ) {
				$this->set($key, $value);
			}
			foreach( $_POST as $key => $value ) {
				$this->set($key, $value);
			}
			foreach( $_FILES as $key => $value ) {
				$this->set($key, $value);
			}
		}

		$request_parts = explode("?", $_SERVER["REQUEST_URI"]);
		if( count($request_parts) > 1 ) {
			$request = $request_parts[0];
		} else {
			$request = $_SERVER["REQUEST_URI"];
		}
		$this->request_uri = $request ?? "";
		$this->request_method = ($this->contains("request_method")) ? strtoupper($this->get("request_method")) : $_SERVER['REQUEST_METHOD'] ?? "";
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
		return preg_split("/\//", $this->getRequestUri(), -1, PREG_SPLIT_NO_EMPTY);
	}
}
