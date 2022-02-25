<?php

namespace core\classes;

/**
 * The Request Type singleton
 * represents the requested URL
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Request {

	// the instance of this class
	private static ?Request $_request = null;
	// the requested URI
	private string $_requestUri;

	/**
	 * The class constructor
	 * sets the current requested uri
	 * calls the method initController()
	 */
	private function __construct() {
		$this->_requestUri = $_SERVER['REQUEST_URI'];
	}

	/**
	 * The initializer for this class
	 * @return Request
	 */
	public static function getInstance(): Request {
		if( static::$_request === null ) {
			static::$_request = new Request();
		}
		return static::$_request;
	}

	/**
	 * Returns the requested uri
	 *
	 * @return string
	 */
	public function getRequestUri(): string {
		return $this->_requestUri;
	}

	/**
	 * Split the requested uri into parts and
	 * returns them as an array
	 *
	 * @return array
	 */
	public function getRequestParts(): array {
		return preg_split("/\//", $this->_requestUri, -1, PREG_SPLIT_NO_EMPTY);
	}
}