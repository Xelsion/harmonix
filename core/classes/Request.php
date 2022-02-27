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

	// The instance of this class
	private static ?Request $_request = null;
	// GET, POST & FILES data from the request
	private array $_form;

	/**
	 * The class constructor
	 * sets the current requested uri
	 * calls the method initController()
	 */
	private function __construct() {
		foreach( $_GET as $key => $value ) {
			$this->_form['GET'][$key] = $value;
		}
		foreach( $_POST as $key => $value ) {
			$this->_form['POST'][$key] = $value;
		}
		foreach( $_FILES as $key => $value ) {
			$this->_form['FILES'][$key] = $value;
		}
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
		return $_SERVER["REQUEST_URI"];
	}

	/**
	 * Returns the requested method
	 *
	 * @return string
	 */
	public function getRequestMethod(): string {
		return $_SERVER["REQUEST_METHOD"];
	}

	/**
	 * Returns the remote IP address
	 *
	 * @return string
	 */
	public function getRemoteIP(): string {
		return $_SERVER["REMOTE_ADDR"];
	}

	public function getGET(): array {
		return $this->_form["GET"] ?? array();
	}

	public function getPOST(): array {
		return $this->_form["GET"] ?? array();
	}

	public function getFILES(): array {
		return $this->_form["FILES"] ?? array();
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