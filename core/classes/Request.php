<?php

namespace core\classes;

class Request {

	private static ?Request $_request = null;
	private string $_requestUri;

	private function __construct() {
		$this->_requestUri = $_SERVER['REQUEST_URI'];
	}

	public function getRequestUri(): string {
		return $this->_requestUri;
	}

	public static function getInstance(): Request {
		if( static::$_request === null ) {
			static::$_request = new Request();
		}
		return static::$_request;
	}

	public function getRequestParts(): array {
		$parts = preg_split("/\//", $this->_requestUri, -1, PREG_SPLIT_NO_EMPTY);
		if( empty($parts) ) {
			$parts[] = "";
		}
		return $parts;
	}
}